<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
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
        $app = Factory::getApplication();

        if ((string) ($parameters['option'] ?? 'com_sportsmanagement') === 'com_sportsmanagement') {
            $itemId = (int) ($parameters['Itemid'] ?? 0);

            if ($itemId <= 0) {
                $active = $app->getMenu()->getActive();

                if (
                    $active !== null
                    && (string) ($active->query['option'] ?? '') === 'com_sportsmanagement'
                ) {
                    $itemId = (int) $active->id;
                }
            }

            if ($itemId <= 0) {
                $itemId = (int) ComponentHelper::getParams('com_sportsmanagement')->get('default_itemid', 0);
            }

            if ($itemId > 0) {
                $parameters['Itemid'] = $itemId;
            } else {
                unset($parameters['Itemid']);
            }
        }

        return Route::_('index.php?' . Uri::buildQuery($parameters), false);
    }
}
