<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 club birthday helper.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
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
            $container = Factory::getContainer();
            /** @var SiteApplication $app */
            $app = $container->get(SiteApplication::class);
            /** @var DatabaseInterface $database */
            $database = $container->get(DatabaseInterface::class);

            return (new ClubBirthdayHelper())->getData($params, $app, $database);
        }

        public static function jsm_birthday_sort(array $clubs, int $sort): array
        {
            return ClubBirthdayHelper::sortClubs($clubs, $sort);
        }
    }
}
