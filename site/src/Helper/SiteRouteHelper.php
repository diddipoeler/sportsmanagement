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
    public static function view(string $view, array $parameters = []): string
    {
        // The requested component and view are authoritative: callers may pass
        // route parameters, but must not redirect this helper to another view.
        return self::query(array_merge($parameters, [
            'option' => 'com_sportsmanagement',
            'view' => $view,
        ]));
    }

    public static function query(array $parameters): string
    {
        if ((string) ($parameters['option'] ?? 'com_sportsmanagement') === 'com_sportsmanagement') {
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
}
