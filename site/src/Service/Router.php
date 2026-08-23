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

            return $segments;
        }

        $segments[] = $view;
        unset($query['view']);

        // Preserve the long-standing positional SportsManagement route format.
        // The helper methods construct these variables in the matching order.
        foreach ($query as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                $segments[] = (string) $value;
                unset($query[$key]);
                continue;
            }

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
     * @param   array  $segments  URL path segments.
     *
     * @return  array
     */
    public function parse(&$segments)
    {
        if (!is_array($segments) || $segments === []) {
            return [];
        }

        $view = $this->normaliseView((string) ($segments[0] ?? ''));
        $defaults = $this->getViewDefaults($view);

        if ($view === '' || $defaults === []) {
            return [];
        }

        $vars = ['view' => $view];
        $position = 1;

        foreach ($defaults as $key => $defaultValue) {
            if (array_key_exists($position, $segments)) {
                $vars[$key] = $segments[$position];
            }

            ++$position;
        }

        // All SportsManagement component segments have been consumed.
        $segments = [];

        return $vars;
    }

    private function getViewDefaults(string $view): array
    {
        if ($view === '' || !class_exists('sportsmanagementHelperRoute')) {
            return [];
        }

        return isset(\sportsmanagementHelperRoute::$views[$view])
            ? (array) \sportsmanagementHelperRoute::$views[$view]
            : [];
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

            if ($query[$key] !== '' && $query[$key] !== null) {
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

    private function normaliseView(string $view): string
    {
        return (string) preg_replace('/[^a-z0-9_]/i', '', strtolower($view));
    }

    private function sameValue($left, $right): bool
    {
        return (string) $left === (string) $right;
    }
}
