<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Build external person-profile links used by site person views.
 */
final class PersonProfileRouteHelper
{
    public static function contact(int $userId, int $database = 0): string
    {
        return self::route([
            'option' => 'com_contact',
            'view' => 'contact',
            'id' => $userId,
            'cfg_which_database' => $database,
        ]);
    }

    public static function cbe(int $userId, int $projectId, int $personId, int $database = 0): string
    {
        return self::route([
            'option' => 'com_cbe',
            'view' => 'userProfile',
            'user' => $userId,
            'jlp' => $projectId,
            'jlpid' => $personId,
            'cfg_which_database' => $database,
        ]);
    }

    private static function route(array $query): string
    {
        $app = Factory::getApplication();
        $defaultItemId = (int) ComponentHelper::getParams('com_sportsmanagement')->get('default_itemid', 0);
        $active = $app->getMenu()->getActive();
        $query['Itemid'] = $active ? (int) $active->id : $defaultItemId;

        return Route::_('index.php?' . Uri::buildQuery($query), false);
    }
}
