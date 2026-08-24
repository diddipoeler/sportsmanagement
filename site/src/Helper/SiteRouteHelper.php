<?php
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
        return self::query(array_merge([
            'option' => 'com_sportsmanagement',
            'view' => $view,
        ], $parameters));
    }

    public static function query(array $parameters): string
    {
        if ((string) ($parameters['option'] ?? 'com_sportsmanagement') === 'com_sportsmanagement') {
            $itemId = (int) ($parameters['Itemid'] ?? 0);

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
