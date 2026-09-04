<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 club birthday helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(ClubBirthdayHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/ClubBirthdayHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(ClubBirthdayHelper::class)) {
    throw new \RuntimeException('SportsManagement native Club Birthday module helper could not be loaded.', 500);
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
