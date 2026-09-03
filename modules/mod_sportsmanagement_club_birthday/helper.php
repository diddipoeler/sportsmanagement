<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 club birthday helper.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(ClubBirthdayHelper::class)) {
    require_once __DIR__ . '/src/Helper/ClubBirthdayHelper.php';
}

if (!class_exists('modSportsmanagementClubBirthdayHelper', false)) {
    final class modSportsmanagementClubBirthdayHelper
    {
        public static function getData(Registry $params): array
        {
            /** @var SiteApplication $app */
            $app = \Joomla\CMS\Factory::getContainer()->get(SiteApplication::class);
            /** @var DatabaseInterface $database */
            $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

            return (new ClubBirthdayHelper())->getData($params, $app, $database);
        }

        public static function jsm_birthday_sort(array $clubs, int $sort): array
        {
            return ClubBirthdayHelper::sortClubs($clubs, $sort);
        }
    }
}
