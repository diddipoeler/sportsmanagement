<?php
/**
 * Joomla 5/6 helper for external person-profile routes.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Build external person-profile links used by site person views.
 */
final class PersonProfileRouteHelper
{
    public static function contact(int $userId, int $database = 0): string
    {
        return SiteRouteHelper::query([
            'option' => 'com_contact',
            'view' => 'contact',
            'id' => $userId,
            'cfg_which_database' => $database,
        ]);
    }

    public static function cbe(int $userId, int $projectId, int $personId, int $database = 0): string
    {
        return SiteRouteHelper::query([
            'option' => 'com_cbe',
            'view' => 'userProfile',
            'user' => $userId,
            'jlp' => $projectId,
            'jlpid' => $personId,
            'cfg_which_database' => $database,
        ]);
    }
}
