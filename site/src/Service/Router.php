<?php
/**
 * Joomla 5/6 component router for SportsManagement.
 *
 * Keeps the established SportsManagement URL format while integrating the
 * component with Joomla's RouterFactory service and menu preprocessing.
 */

namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

class Router extends RouterBase
{
    private const COMPONENT = 'com_sportsmanagement';

    public function __construct(
        CMSApplicationInterface $app,
        AbstractMenu $menu,
        ?CategoryFactoryInterface $categoryFactory = null,
        ?DatabaseInterface $db = null
    ) {
        parent::__construct($app, $menu);

        // The route definition is still shared with the legacy presentation
        // layer while the individual views are migrated incrementally.
        LegacyPresentationLoader::register();
    }

    /**
     * Select the best matching SportsManagement menu item before Joomla builds
     * the route. This also corrects legacy helper URLs which carried the active
     * Itemid instead of the Itemid belonging to the target view.
     *
     * @param   array  $query  URL query variables.
     *
     * @return  array
     */
    public function preprocess($query)
    {
        $query = parent::preprocess($query);

        if (!is_array($query)) {
            return $query;
        }

        $option = (string) ($query['option'] ?? self::COMPONENT);
        $view = $this->normaliseView((string) ($query['view'] ?? ''));

        if ($option !== self::COMPONENT || $view === '') {
            return $query;
        }

        // A legacy/default Itemid must never turn a SportsManagement URL into
        // an unrelated or external menu link. Keep it only when it actually
        // belongs to this component, then let the normal matcher improve it.
        $currentItemId = (int) ($query['Itemid'] ?? 0);

        if ($currentItemId > 0) {
            $currentItem = $this->menu->getItem($currentItemId);

            if (!$this->isSportsManagementMenuItem($currentItem)) {
                unset($query['Itemid']);
            }
        }

        $item = $this->findBestMenuItem($query, $view);

        if ($item !== null) {
            $query['Itemid'] = (int) $item->id;
        }

        return $query;
    }

    /**
     * Build SportsManagement SEF segments.
     *
     * Exact menu links intentionally produce no additional component segment;
     * Joomla can then use the menu alias itself as the canonical URL.
     * Non-menu links keep the historical view/parameter segment format.
     *
     * @param   array  $query  URL query variables.
     *
     * @return  array
     */
    public function build(&$query)
    {
        $segments = [];
        $menuItem = $this->getMenuItemFromQuery($query);
        $menuQuery = $menuItem !== null && isset($menuItem->query) ? (array) $menuItem->query : [];
        $view = $this->normaliseView((string) ($query['view'] ?? ($menuQuery['view'] ?? '')));

        if ($view === '') {
            return $segments;
        }

        $defaults = $this->getViewDefaults($view);

        if ($menuItem !== null && $this->menuItemRepresentsQuery($menuItem, $query, $view, $defaults)) {
            unset($query['view']);

            foreach ($menuQuery as $key => $value) {
                if ($key === 'option' || $key === 'Itemid') {
                    continue;
                }

                if (array_key_exists($key, $query) && $this->sameValue($query[$key], $value)) {
                    unset($query[$key]);
                }
            }

            // Legacy helpers often add zero-valued optional arguments. They do
            // not make the target different from the menu item and should not
            // survive as query-string noise on an otherwise exact menu route.
            foreach ($defaults as $key => $defaultValue) {
                if (array_key_exists($key, $query) && $this->isEmptyRouteValue($query[$key])) {
                    unset($query[$key]);
                }
            }

            return $segments;
        }

        $segments[] = $view;
        unset($query['view']);

        // The historic SportsManagement route is positional. If a later
        // parameter is present, every position before it must be represented as
        // well; otherwise parse() would assign the value to the wrong key. Use
        // the menu value first and a stable zero placeholder for empty defaults.
        if ($defaults !== []) {
            $defaultKeys = array_keys($defaults);
            $lastIndex = -1;

            foreach ($defaultKeys as $index => $key) {
                if (array_key_exists($key, $query)) {
                    $lastIndex = $index;
                }
            }

            if ($lastIndex >= 0) {
                foreach ($defaultKeys as $index => $key) {
                    if ($index > $lastIndex) {
                        break;
                    }

                    if (array_key_exists($key, $query)) {
                        $value = $query[$key];
                        unset($query[$key]);
                    } elseif (array_key_exists($key, $menuQuery)) {
                        $value = $menuQuery[$key];
                    } else {
                        $value = $this->getRouteDefaultValue($defaults[$key]);
                    }

                    $segments[] = (string) $value;
                }
            }
        }

        // Remove remaining variables which are already represented by the menu
        // item. Non-view variables which differ stay in the query string.
        foreach ($query as $key => $value) {
            if (
                $menuItem !== null
                && $key !== 'Itemid'
                && $key !== 'option'
                && array_key_exists($key, $menuQuery)
                && $this->sameValue($value, $menuQuery[$key])
            ) {
                unset($query[$key]);
            }
        }

        return $segments;
    }

    /**
     * Parse the historical SportsManagement positional SEF segments.
     *
     * Views which have already moved away from the legacy route definition are
     * still valid component views. They use the view name as their single SEF
     * segment while any additional parameters remain in the query string.
     *
     * @param   array  $segments  URL path segments.
     *
     * @return  array
     */
    public function parse(&$segments)
    {
        if (!is_array($segments) || $segments === []) {
            return [];
        }

        $segments = array_values($segments);
        $view = $this->normaliseView((string) ($segments[0] ?? ''));

        if ($view === '' || !$this->isKnownSiteView($view)) {
            return [];
        }

        $defaults = $this->getViewDefaults($view);
        $vars = ['view' => $view];

        if ($defaults === []) {
            array_shift($segments);

            return $vars;
        }

        $position = 1;

        foreach ($defaults as $key => $defaultValue) {
            if (array_key_exists($position, $segments)) {
                $vars[$key] = $segments[$position];
            }

            ++$position;
        }

        // Consume only the segments understood by this component router. Extra
        // path segments remain visible to Joomla instead of being silently lost.
        $segments = array_slice($segments, $position);

        return $vars;
    }

    private function getViewDefaults(string $view): array
    {
        if (!$this->hasLegacyRouteDefinition($view)) {
            return [];
        }

        return (array) \sportsmanagementHelperRoute::$views[$view];
    }

    private function hasLegacyRouteDefinition(string $view): bool
    {
        return $view !== ''
            && class_exists('sportsmanagementHelperRoute')
            && isset(\sportsmanagementHelperRoute::$views[$view]);
    }

    private function isKnownSiteView(string $view): bool
    {
        if ($this->hasLegacyRouteDefinition($view)) {
            return true;
        }

        $legacyViewPath = JPATH_SITE . '/components/com_sportsmanagement/views/' . $view;

        if (is_dir($legacyViewPath)) {
            return true;
        }

        $classPrefix = 'Diddipoeler\\Component\\SportsManagement\\Site\\View\\'
            . ucfirst($view)
            . '\\';

        foreach (['HtmlView', 'RawView', 'PdfView'] as $viewClass) {
            if (class_exists($classPrefix . $viewClass)) {
                return true;
            }
        }

        return false;
    }

    private function getRouteDefaultValue($defaultValue)
    {
        return $this->isEmptyRouteValue($defaultValue) ? '0' : $defaultValue;
    }

    private function getMenuItemFromQuery(array $query): ?object
    {
        $itemId = (int) ($query['Itemid'] ?? 0);

        if ($itemId <= 0) {
            return null;
        }

        $item = $this->menu->getItem($itemId);

        return $this->isSportsManagementMenuItem($item) ? $item : null;
    }

    private function findBestMenuItem(array $query, string $view): ?object
    {
        $items = $this->getSportsManagementMenuItems();

        if ($items === []) {
            return null;
        }

        $authorisedLevels = null;

        if (method_exists($this->app, 'getIdentity')) {
            $identity = $this->app->getIdentity();

            if ($identity !== null && method_exists($identity, 'getAuthorisedViewLevels')) {
                $authorisedLevels = array_map('intval', (array) $identity->getAuthorisedViewLevels());
            }
        }

        $best = null;
        $bestScore = PHP_INT_MIN;
        $currentItemId = (int) ($query['Itemid'] ?? 0);

        foreach ($items as $item) {
            if (!$this->isSportsManagementMenuItem($item)) {
                continue;
            }

            if (isset($item->published) && (int) $item->published !== 1) {
                continue;
            }

            if (
                is_array($authorisedLevels)
                && isset($item->access)
                && !in_array((int) $item->access, $authorisedLevels, true)
            ) {
                continue;
            }

            $menuQuery = isset($item->query) ? (array) $item->query : [];
            $menuView = $this->normaliseView((string) ($menuQuery['view'] ?? ''));

            if ($menuView !== $view) {
                continue;
            }

            $score = 100;
            $conflict = false;

            foreach ($menuQuery as $key => $value) {
                if (in_array($key, ['option', 'view', 'Itemid'], true)) {
                    continue;
                }

                if (!array_key_exists($key, $query)) {
                    --$score;
                    continue;
                }

                if (!$this->sameValue($query[$key], $value)) {
                    $conflict = true;
                    break;
                }

                $score += 10;
            }

            if ($conflict) {
                continue;
            }

            if ((int) ($item->id ?? 0) === $currentItemId) {
                ++$score;
            }

            if ($score > $bestScore) {
                $best = $item;
                $bestScore = $score;
            }
        }

        return $best;
    }

    private function getSportsManagementMenuItems(): array
    {
        $items = $this->menu->getItems('component', self::COMPONENT);

        if (is_array($items) && $items !== []) {
            return $items;
        }

        // Some menu implementations expose the numeric component_id instead.
        $component = ComponentHelper::getComponent(self::COMPONENT);
        $items = $this->menu->getItems('component_id', (int) $component->id);

        return is_array($items) ? $items : [];
    }

    private function menuItemRepresentsQuery(object $item, array $query, string $view, array $defaults): bool
    {
        $menuQuery = isset($item->query) ? (array) $item->query : [];

        if ($this->normaliseView((string) ($menuQuery['view'] ?? '')) !== $view) {
            return false;
        }

        foreach ($menuQuery as $key => $value) {
            if (in_array($key, ['option', 'view', 'Itemid'], true) || !array_key_exists($key, $query)) {
                continue;
            }

            if (!$this->sameValue($query[$key], $value)) {
                return false;
            }
        }

        // A view-specific value that is not represented by the menu item means
        // this is a child/dynamic URL and therefore still needs route segments.
        // Zero-valued legacy helper defaults are treated as empty here.
        foreach ($defaults as $key => $defaultValue) {
            if (!array_key_exists($key, $query)) {
                continue;
            }

            if (array_key_exists($key, $menuQuery)) {
                if (!$this->sameValue($query[$key], $menuQuery[$key])) {
                    return false;
                }

                continue;
            }

            if (!$this->isEmptyRouteValue($query[$key])) {
                return false;
            }
        }

        return true;
    }

    private function isSportsManagementMenuItem($item): bool
    {
        if (!is_object($item)) {
            return false;
        }

        if (isset($item->component) && (string) $item->component === self::COMPONENT) {
            return true;
        }

        $query = isset($item->query) ? (array) $item->query : [];

        return (string) ($query['option'] ?? '') === self::COMPONENT;
    }

    private function isEmptyRouteValue($value): bool
    {
        return $value === null || $value === '' || $value === 0 || $value === '0';
    }

    private function normaliseView(string $view): string
    {
        return (string) preg_replace('/[^a-z0-9_]/i', '', strtolower($view));
    }

    private function sameValue($left, $right): bool
    {
        return (string) $left === (string) $right;
    }
}
