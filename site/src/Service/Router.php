<?php
/**
 * Joomla 5/6 component router for SportsManagement.
 *
 * Keeps the established SportsManagement URL format while integrating the
 * component with Joomla's RouterFactory service and menu preprocessing.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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

    /**
     * Initialise the Joomla 5/6 frontend router with its active menu.
     *
     * Uses Joomla's application and menu to resolve canonical component links.
     * Optional category-factory and database parameters retain the framework's
     * compatible constructor signature; routing itself uses the menu service.
     *
     * @param CMSApplicationInterface $app Current Joomla frontend application.
     * @param AbstractMenu $menu Active menu repository for SEF URL selection.
     * @param CategoryFactoryInterface|null $categoryFactory Optional category factory.
     * @param DatabaseInterface|null $db Optional database connection.
     */
    public function __construct(
        CMSApplicationInterface $app,
        AbstractMenu $menu,
        ?CategoryFactoryInterface $categoryFactory = null,
        ?DatabaseInterface $db = null
    ) {
        parent::__construct($app, $menu);
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

        // Reject non-scalar URL selectors before Joomla's route normalisation.
        // Casting arrays to strings emits PHP warnings and can misroute requests.
        $rawOption = $query['option'] ?? self::COMPONENT;
        $rawView = $query['view'] ?? '';

        if (!is_string($rawOption) || !is_string($rawView)) {
            throw new \InvalidArgumentException('Invalid SportsManagement route selector.', 400);
        }

        $option = $rawOption;
        $view = $this->normaliseView($rawView);

        if ($option !== self::COMPONENT || $view === '') {
            return $query;
        }

        // A legacy/default Itemid must never turn a SportsManagement URL into
        // an unrelated or external menu link. Keep it only when it actually
        // belongs to this component, then let the normal matcher improve it.
        $rawItemId = $query['Itemid'] ?? 0;

        // Invalid menu selectors must not trigger array-to-integer casts.
        if (!is_int($rawItemId) && !is_string($rawItemId)) {
            unset($query['Itemid']);
            $rawItemId = 0;
        }

        $currentItemId = ctype_digit((string) $rawItemId) ? (int) $rawItemId : 0;

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
        $rawView = $query['view'] ?? ($menuQuery['view'] ?? '');

        if (!is_string($rawView)) {
            throw new \InvalidArgumentException('Invalid SportsManagement route view.', 400);
        }

        $view = $this->normaliseView($rawView);

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

                    // Positional route segments must be scalar; arrays trigger warnings.
                    if (!is_scalar($value)) {
                        throw new \InvalidArgumentException('Invalid SportsManagement route parameter.', 400);
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
     * Views without a positional schema are still valid component views. They
     * use the view name as their single SEF segment while any additional
     * parameters remain in the query string.
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
        if (!is_string($segments[0])) {
            throw new \InvalidArgumentException('Invalid SportsManagement route segment.', 400);
        }

        $view = $this->normaliseView($segments[0]);

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
                // URL path parts must be strings; do not forward arrays to views.
                if (!is_string($segments[$position])) {
                    throw new \InvalidArgumentException('Invalid SportsManagement route segment.', 400);
                }

                $vars[$key] = $segments[$position];
            }

            ++$position;
        }

        // Consume only the segments understood by this component router. Extra
        // path segments remain visible to Joomla instead of being silently lost.
        $segments = array_slice($segments, $position);

        return $vars;
    }

    /**
     * Look up positional route parameters for a SportsManagement view.
     *
     * Delegates to the shared schema used by the modern URL builder and parser
     * so legacy segment ordering remains stable during Joomla 5/6 migration.
     *
     * @param string $view Normalised frontend view.
     * @return array<string, mixed> Ordered route argument defaults.
     */
    private function getViewDefaults(string $view): array
    {
        return SiteRouteSchema::defaults($view);
    }

    /**
     * Check whether a view has a positional route specification.
     *
     * Empty names are rejected and other names are checked against the
     * explicit SportsManagement site route schema.
     *
     * @param string $view Normalised view identifier.
     * @return bool Whether the router has a definition for this view.
     */
    private function hasRouteDefinition(string $view): bool
    {
        return $view !== '' && SiteRouteSchema::has($view);
    }

    /**
     * Recognise views supported by the route schema, legacy tree or native MVC.
     *
     * Accepts a defined route, an installed historical view directory, or
     * a loadable namespaced HTML, RAW or PDF view class. Unknown URLs do not
     * acquire arbitrary component view routes.
     *
     * @param string $view Normalised frontend view name.
     * @return bool True if an installed site view can handle the route.
     */
    private function isKnownSiteView(string $view): bool
    {
        if ($this->hasRouteDefinition($view)) {
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

    /**
     * Convert optional empty route parameters into a stable positional segment.
     *
     * The historical URL schema expects a zero placeholder instead of an
     * absent value when a later positional parameter is supplied.
     *
     * @param mixed $defaultValue Route schema value or optional placeholder.
     * @return mixed Original value, or string zero for an empty value.
     */
    private function getRouteDefaultValue($defaultValue)
    {
        return $this->isEmptyRouteValue($defaultValue) ? '0' : $defaultValue;
    }

    /**
     * Resolve the selected Joomla menu item from a validated Itemid.
     *
     * Rejects arrays, partial numeric strings and non-positive identifiers,
     * then returns only menu entries belonging to com_sportsmanagement.
     *
     * @param array $query URL query variables including an optional Itemid.
     * @return object|null Matching component menu item, if valid.
     */
    private function getMenuItemFromQuery(array $query): ?object
    {
        $rawItemId = $query['Itemid'] ?? 0;

        // Menu IDs must be unsigned decimal values, not arrays or partial numbers.
        if ((!is_int($rawItemId) && !is_string($rawItemId))
            || !ctype_digit((string) $rawItemId)
        ) {
            return null;
        }

        $itemId = (int) $rawItemId;

        if ($itemId <= 0) {
            return null;
        }

        $item = $this->menu->getItem($itemId);

        return $this->isSportsManagementMenuItem($item) ? $item : null;
    }

    /**
     * Score eligible component menu items for a requested SportsManagement URL.
     *
     * Reads the available menu entries, honours published and access-level
     * metadata and favours routes matching the requested view and parameters.
     * Unusable menu IDs and malformed metadata are ignored without unsafe
     * numeric or string casts.
     *
     * @param array $query Route query values used to assess candidates.
     * @param string $view Normalised target view.
     * @return object|null Highest matching accessible menu item, if any.
     */
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
        $rawItemId = $query['Itemid'] ?? 0;
        $currentItemId = (is_int($rawItemId) || is_string($rawItemId))
            && ctype_digit((string) $rawItemId)
            ? (int) $rawItemId
            : 0;

        foreach ($items as $item) {
            if (!$this->isSportsManagementMenuItem($item)) {
                continue;
            }

            // The selected item becomes query['Itemid'] in preprocess().
            // Never select an item whose identifier cannot be used as an ID.
            $candidateId = $item->id ?? null;
            if ((!is_int($candidateId) && !is_string($candidateId))
                || !ctype_digit((string) $candidateId)
                || (int) $candidateId <= 0
            ) {
                continue;
            }

            // Malformed menu metadata must not be cast from arrays or objects.
            if (isset($item->published)
                && (!is_scalar($item->published) || (string) $item->published !== '1')
            ) {
                continue;
            }

            if (
                is_array($authorisedLevels)
                && isset($item->access)
                && (!is_scalar($item->access)
                    || !ctype_digit((string) $item->access)
                    || !in_array((int) $item->access, $authorisedLevels, true))
            ) {
                continue;
            }

            $menuQuery = isset($item->query) ? (array) $item->query : [];
            $menuViewValue = $menuQuery['view'] ?? '';
            if (!is_string($menuViewValue)) {
                continue;
            }

            $menuView = $this->normaliseView($menuViewValue);

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

            if (($currentItemId > 0)
                && (is_int($candidateId) || is_string($candidateId))
                && ctype_digit((string) $candidateId)
                && (int) $candidateId === $currentItemId
            ) {
                ++$score;
            }

            if ($score > $bestScore) {
                $best = $item;
                $bestScore = $score;
            }
        }

        return $best;
    }

    /**
     * Obtain menu entries owned by this component from Joomla's active menu.
     *
     * Uses component-name lookup first and falls back to the numeric component
     * ID when required by the menu implementation. Invalid extension metadata
     * yields an empty list, never an unrestricted menu search.
     *
     * @return array Menu entries linked to com_sportsmanagement.
     */
    private function getSportsManagementMenuItems(): array
    {
        $items = $this->menu->getItems('component', self::COMPONENT);

        if (is_array($items) && $items !== []) {
            return $items;
        }

        // Some menu implementations expose the numeric component_id instead.
        $component = ComponentHelper::getComponent(self::COMPONENT);
        $componentId = $component->id ?? null;

        // Missing component metadata cannot identify a numeric Joomla menu target.
        if ((!is_int($componentId) && !is_string($componentId))
            || !ctype_digit((string) $componentId)
            || (int) $componentId <= 0
        ) {
            return [];
        }

        $items = $this->menu->getItems('component_id', (int) $componentId);

        return is_array($items) ? $items : [];
    }

    /**
     * Determine whether a menu route fully expresses the requested URL.
     *
     * Compares the menu view and supported positional parameters with the
     * query, disregarding only optional empty legacy defaults. Child records
     * with meaningful extra arguments still need route segments.
     *
     * @param object $item Candidate Joomla component menu entry.
     * @param array $query Requested URL parameters.
     * @param string $view Normalised view name.
     * @param array $defaults View-specific optional parameter defaults.
     * @return bool True when the menu alias can replace positional segments.
     */
    private function menuItemRepresentsQuery(object $item, array $query, string $view, array $defaults): bool
    {
        $menuQuery = isset($item->query) ? (array) $item->query : [];

        $menuView = $menuQuery['view'] ?? '';
        if (!is_string($menuView) || $this->normaliseView($menuView) !== $view) {
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

    /**
     * Verify that a Joomla menu record belongs to SportsManagement.
     *
     * Accepts either an exact component property or a matching query option;
     * refuses non-object entries and avoids converting malformed metadata.
     *
     * @param mixed $item Candidate menu row.
     * @return bool Whether the row belongs to this component.
     */
    private function isSportsManagementMenuItem($item): bool
    {
        if (!is_object($item)) {
            return false;
        }

        // Joomla menu data can be malformed; never stringify arrays or objects.
        if (isset($item->component) && is_string($item->component)
            && $item->component === self::COMPONENT
        ) {
            return true;
        }

        $query = isset($item->query) ? (array) $item->query : [];

        return ($query['option'] ?? null) === self::COMPONENT;
    }

    /**
     * Identify optional route values equivalent to the legacy zero placeholder.
     *
     * Distinguishes truly empty values from valid nonzero and boolean values
     * when choosing whether to omit positional query arguments.
     *
     * @param mixed $value Value to classify.
     * @return bool True for null, empty string, zero or string zero.
     */
    private function isEmptyRouteValue($value): bool
    {
        return $value === null || $value === '' || $value === 0 || $value === '0';
    }

    /**
     * Normalise the frontend view selector for Joomla route lookup.
     *
     * Lowercases view names and retains only ASCII letters, digits and
     * underscores so a request cannot inject filesystem path separators.
     *
     * @param string $view Requested view identifier.
     * @return string Canonical safe route view name.
     */
    private function normaliseView(string $view): string
    {
        return (string) preg_replace('/[^a-z0-9_]/i', '', strtolower($view));
    }

    /**
     * Compare scalar route values without PHP 8 array conversion warnings.
     *
     * Refuses arrays and objects, including cases that would otherwise compare
     * as the string Array, and compares legitimate scalar or null values
     * through the component's historical string-valued URL semantics.
     *
     * @param mixed $left First query or menu value.
     * @param mixed $right Second query or menu value.
     * @return bool Whether both safe values encode identically.
     */
    private function sameValue($left, $right): bool
    {
        // Do not treat arrays as the identical string "Array" in PHP 8.
        if (!is_scalar($left) && $left !== null) {
            return false;
        }

        if (!is_scalar($right) && $right !== null) {
            return false;
        }

        return (string) $left === (string) $right;
    }
}
