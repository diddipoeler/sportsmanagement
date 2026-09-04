<?php
/**
 * Legacy router compatibility bridge for SportsManagement.
 *
 * Joomla 5/6 obtains the component router through RouterFactory and
 * Diddipoeler\Component\SportsManagement\Site\Service\Router. These proxy
 * symbols are retained for third-party extensions which still call the
 * historical component routing functions directly.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\Router as SportsManagementRouterService;
use Diddipoeler\Component\SportsManagement\Site\Service\SiteRouteSchema;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

// Joomla 5 can legitimately reach this file through LegacyComponent before the
// component PSR-4 namespace has been activated (for example after an upgrade or
// when the service provider cannot be completed). Make the compatibility bridge
// self-contained instead of assuming that the native router already autoloads.
if (!class_exists(SportsManagementRouterService::class) || !class_exists(SiteRouteSchema::class)) {
    $routeSchema = __DIR__ . '/src/Service/SiteRouteSchema.php';
    $nativeRouter = __DIR__ . '/src/Service/Router.php';

    if (is_file($routeSchema)) {
        require_once $routeSchema;
    }

    if (is_file($nativeRouter)) {
        require_once $nativeRouter;
    }
}

if (!class_exists(SportsManagementRouterService::class) || !class_exists(SiteRouteSchema::class)) {
    throw new \RuntimeException('SportsManagement native site router could not be loaded.', 500);
}

/**
 * Backward-compatible class name used by older SportsManagement integrations.
 *
 * RouterInterface is declared explicitly because Joomla 5 LegacyComponent uses
 * reflection on this global bridge class to decide whether it can use the class
 * router directly or must fall back to RouterLegacy.
 */
class SportsmanagementRouter extends SportsManagementRouterService implements RouterInterface
{
    private const COMPONENT = 'com_sportsmanagement';

    public function __construct(?CMSApplicationInterface $app = null, ?AbstractMenu $menu = null)
    {
        $app ??= Factory::getContainer()->get(SiteApplication::class);
        $menu ??= $app->getMenu();

        // Joomla 5 LegacyComponent::createRouter() passes its application and
        // menu explicitly. Honour that context instead of resolving a second
        // application/menu pair from Factory. The optional factory/database
        // arguments remain null because this router does not use them.
        parent::__construct(
            $app,
            $menu,
            null,
            null
        );
    }

    /**
     * Correct Joomla 5 menu selection when language filtering is enabled.
     *
     * The shared native router deliberately handles generic menu scoring. On
     * Joomla 5, however, getItems() can still expose equally matching component
     * items from several menu languages. If the generic matcher picks a foreign
     * language Itemid, SiteRouter builds the URL below the wrong menu alias.
     * Keep the shared Joomla 6 path untouched and only repair an incompatible
     * Itemid in this Joomla 5 compatibility wrapper.
     */
    public function preprocess($query)
    {
        $query = parent::preprocess($query);

        if (!is_array($query)
            || (string) ($query['option'] ?? self::COMPONENT) !== self::COMPONENT
            || !method_exists($this->app, 'getLanguageFilter')
            || !$this->app->getLanguageFilter()
        ) {
            return $query;
        }

        $view = (string) preg_replace('/[^a-z0-9_]/i', '', strtolower((string) ($query['view'] ?? '')));

        if ($view === '') {
            return $query;
        }

        $itemId = (int) ($query['Itemid'] ?? 0);
        $item = $itemId > 0 ? $this->menu->getItem($itemId) : null;

        if ($this->isLanguageCompatibleMenuItem($item, $view)) {
            return $query;
        }

        $replacement = $this->findLanguageCompatibleMenuItem($query, $view);

        if ($replacement !== null) {
            $query['Itemid'] = (int) $replacement->id;
        } else {
            unset($query['Itemid']);
        }

        return $query;
    }

    /**
     * Preserve the historical Joomla 5 component-parse contract.
     *
     * The old SportsManagement router seeded its result with the active menu
     * query and consumed the complete component route once a known view had
     * been recognised. Joomla 5 is particularly sensitive to an unconsumed
     * path because SiteRouter rejects any path left after component parsing.
     * Joomla 6 uses the native RouterFactory class directly and therefore does
     * not pass through this compatibility method.
     */
    public function parse(&$segments)
    {
        $vars = parent::parse($segments);
        $view = (string) ($vars['view'] ?? '');

        if ($view === '') {
            return $vars;
        }

        $active = $this->menu->getActive();

        if (is_object($active)) {
            $menuQuery = isset($active->query) ? (array) $active->query : [];
            $component = (string) ($active->component ?? ($menuQuery['option'] ?? ''));

            if ($component === self::COMPONENT) {
                $vars = array_merge($menuQuery, $vars);
            }
        }

        // The historical SportsManagement parser consumed the full component
        // segment list. Keep that behaviour on Joomla 5 so obsolete trailing
        // positional segments cannot turn an otherwise valid route into a 404.
        $segments = [];

        return $vars;
    }

    private function isLanguageCompatibleMenuItem($item, string $view): bool
    {
        if (!is_object($item)) {
            return false;
        }

        $menuQuery = isset($item->query) ? (array) $item->query : [];
        $component = (string) ($item->component ?? ($menuQuery['option'] ?? ''));
        $menuView = (string) preg_replace('/[^a-z0-9_]/i', '', strtolower((string) ($menuQuery['view'] ?? '')));

        if ($component !== self::COMPONENT || $menuView !== $view) {
            return false;
        }

        $language = (string) ($item->language ?? '*');
        $currentLanguage = $this->getCurrentLanguageTag();

        return $language === '*' || $currentLanguage === '' || $language === $currentLanguage;
    }

    private function findLanguageCompatibleMenuItem(array $query, string $view): ?object
    {
        $items = $this->menu->getItems('component', self::COMPONENT);

        if (!is_array($items) || $items === []) {
            $component = ComponentHelper::getComponent(self::COMPONENT);
            $items = $this->menu->getItems('component_id', (int) $component->id);
        }

        if (!is_array($items) || $items === []) {
            return null;
        }

        $currentLanguage = $this->getCurrentLanguageTag();
        $currentItemId = (int) ($query['Itemid'] ?? 0);
        $authorisedLevels = null;

        if (method_exists($this->app, 'getIdentity')) {
            $identity = $this->app->getIdentity();

            if ($identity !== null && method_exists($identity, 'getAuthorisedViewLevels')) {
                $authorisedLevels = array_map('intval', (array) $identity->getAuthorisedViewLevels());
            }
        }

        $best = null;
        $bestScore = PHP_INT_MIN;

        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }

            if (isset($item->published) && (int) $item->published !== 1) {
                continue;
            }

            if (is_array($authorisedLevels)
                && isset($item->access)
                && !in_array((int) $item->access, $authorisedLevels, true)
            ) {
                continue;
            }

            $menuQuery = isset($item->query) ? (array) $item->query : [];
            $component = (string) ($item->component ?? ($menuQuery['option'] ?? ''));
            $menuView = (string) preg_replace('/[^a-z0-9_]/i', '', strtolower((string) ($menuQuery['view'] ?? '')));
            $language = (string) ($item->language ?? '*');

            if ($component !== self::COMPONENT || $menuView !== $view) {
                continue;
            }

            if ($currentLanguage !== '' && $language !== '*' && $language !== $currentLanguage) {
                continue;
            }

            $score = $language === $currentLanguage ? 1000 : 500;
            $conflict = false;

            foreach ($menuQuery as $key => $value) {
                if (in_array($key, ['option', 'view', 'Itemid', 'lang'], true)) {
                    continue;
                }

                if (!array_key_exists($key, $query)) {
                    --$score;
                    continue;
                }

                if ((string) $query[$key] !== (string) $value) {
                    $conflict = true;
                    break;
                }

                $score += 10;
            }

            if ($conflict) {
                continue;
            }

            if ((int) ($item->id ?? 0) === $currentItemId) {
                $score += 5;
            }

            if ($best === null
                || $score > $bestScore
                || ($score === $bestScore && (int) ($item->id ?? PHP_INT_MAX) < (int) ($best->id ?? PHP_INT_MAX))
            ) {
                $best = $item;
                $bestScore = $score;
            }
        }

        return $best;
    }

    private function getCurrentLanguageTag(): string
    {
        if (!method_exists($this->app, 'getLanguage')) {
            return '';
        }

        $language = $this->app->getLanguage();

        return is_object($language) && method_exists($language, 'getTag')
            ? (string) $language->getTag()
            : '';
    }
}

/**
 * Return a native SportsManagement router for historical routing functions.
 *
 * Do not call bootComponent() from this compatibility bridge. Joomla 5 may be
 * executing this file from LegacyComponent::createRouter(); booting the same
 * component again from here can re-enter the legacy router bootstrap. The
 * bridge class above already wraps the same native Router implementation Joomla
 * 5/6 receives from RouterFactory, so a direct instance is both equivalent and
 * safe on the legacy path.
 */
function sportsmanagementGetRouter(): RouterInterface
{
    static $router = null;

    if (!$router instanceof RouterInterface) {
        $router = new SportsmanagementRouter();
    }

    return $router;
}

/**
 * Legacy build proxy.
 *
 * @param   array  $query  URL query variables.
 *
 * @return  array
 */
function SportsmanagementBuildRoute(&$query)
{
    $router = sportsmanagementGetRouter();
    $query = $router->preprocess($query);

    return $router->build($query);
}

/**
 * Legacy parse proxy.
 *
 * Joomla 5 RouterLegacy passes its component segments by reference and expects
 * the component parser to remove every segment it consumes. Keep that reference
 * intact while delegating to the native router; otherwise Joomla 5 treats the
 * already parsed SportsManagement segments as an unconsumed trailing path.
 *
 * @param   array  $segments  URL path segments.
 *
 * @return  array
 */
function SportsmanagementParseRoute(&$segments)
{
    return sportsmanagementGetRouter()->parse($segments);
}
