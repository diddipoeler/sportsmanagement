<?php
/**
 * Central Joomla 5/6 site routing helper for SportsManagement.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Build Joomla 5/6 site routes without loading the legacy route helper.
 */
final class SiteRouteHelper
{
    /**
     * Build a Joomla route to the specified SportsManagement frontend view.
     * The component option and view are authoritative even when additional
     * caller parameters contain conflicting keys. Rejects malformed view
     * identifiers before they can become route query parameters.
     *
     * @param string $view Target SportsManagement view.
     * @param array<string,mixed> $parameters Optional route query parameters.
     * @return string Routed frontend URL.
     */
    public static function view(string $view, array $parameters = []): string
    {
        // Joomla view names are identifiers, never paths or query fragments.
        // Refuse malformed caller input rather than generating an unsafe route.
        if (!self::isViewName($view)) {
            throw new \InvalidArgumentException('Invalid SportsManagement frontend view name.');
        }

        // The requested component and view are authoritative: callers may pass
        // route parameters, but must not redirect this helper to another view.
        return self::query(array_merge($parameters, [
            'option' => 'com_sportsmanagement',
            'view' => $view,
        ]));
    }

    /**
     * Build a Joomla router URL from validated component parameters.
     *
     * Validates the component option and an explicit SportsManagement
     * view and menu Itemid before delegating query encoding to Joomla's Uri
     * and Route services. An invalid view in a generic component query raises
     * an exception rather than producing nested view[] query parameters or
     * an unintended route. Explicit null or malformed component options
     * also fail closed rather than silently switching the destination.
     * Other component route parameters remain unchanged.
     *
     * @param array<string,mixed> $parameters Route query components.
     * @return string Joomla frontend URL.
     * @throws \InvalidArgumentException When an explicit option or SportsManagement view is malformed.
     */
    public static function query(array $parameters): string
    {
        // A component route needs an explicit, scalar option. Do not let
        // malformed nested input become an option[] query parameter.
        $option = array_key_exists('option', $parameters) ? $parameters['option'] : 'com_sportsmanagement';

        // Invalid explicit component names must not silently route visitors to
        // SportsManagement instead of the component requested by the caller.
        if (!is_string($option) || strlen($option) > 100
            || preg_match('/^com_[a-z0-9_]+$/iD', $option) !== 1) {
            throw new \InvalidArgumentException('Invalid Joomla component route option.');
        }

        $option = strtolower($option);
        $parameters['option'] = $option;

        if ($option === 'com_sportsmanagement') {
            // The generic URL builder is also used for navigation links.
            // Apply the same view boundary as view() to untrusted options.
            if (array_key_exists('view', $parameters)
                && !self::isViewName($parameters['view'])) {
                throw new \InvalidArgumentException('Invalid SportsManagement frontend view name.');
            }

            $requestedItemId = $parameters['Itemid'] ?? null;
            $itemId = 0;

            // Do not turn an invalid menu ID (e.g. "42junk", booleans,
            // arrays or an overflowing number) into an unrelated menu link.
            if (is_int($requestedItemId)) {
                $itemId = max(0, $requestedItemId);
            } elseif (is_string($requestedItemId)
                && preg_match('/^[0-9]+$/D', trim($requestedItemId))) {
                $digits = ltrim(trim($requestedItemId), '0');

                if ($digits !== '') {
                    $validated = filter_var($digits, FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 1],
                    ]);
                    $itemId = $validated === false ? 0 : $validated;
                }
            }

            // Only preserve an explicitly requested menu item. When no positive
            // Itemid is supplied, leave menu selection to the component router's
            // preprocess() method so it can choose a menu item matching the
            // target view and route parameters instead of reusing the active one.
            if ($itemId > 0) {
                $parameters['Itemid'] = $itemId;
            } else {
                unset($parameters['Itemid']);
            }
        }

        return Route::_('index.php?' . Uri::buildQuery($parameters), false);
    }

    /**
     * Recognize a Joomla component view identifier without coercing its type.
     *
     * Joomla view names are bounded single identifiers, never paths, query
     * fragments or nested values. Reuse this check for both direct
     * SiteRouteHelper::view() calls and generic component query construction
     * so malformed user/module settings cannot create alternate view routes.
     *
     * @param mixed $name Candidate view name or untrusted query value.
     * @return bool True only for a nonempty, short ASCII identifier.
     */
    private static function isViewName(mixed $name): bool
    {
        return is_string($name)
            && $name !== ''
            && strlen($name) <= 100
            && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $name) === 1;
    }
}
