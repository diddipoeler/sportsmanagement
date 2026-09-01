<?php
/** Legacy compatibility facade for the Joomla 5/6 birthday module helper. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(BirthdayHelper::class)) {
    require_once __DIR__ . '/src/Helper/BirthdayHelper.php';
}

if (!function_exists('jsm_birthday_sort')) {
    function jsm_birthday_sort(array $rows, $arguments = '-', $keys = true): array
    {
        return BirthdayHelper::sortPersons($rows, $arguments, (bool) $keys);
    }
}

if (!class_exists('modSportsmanagementBirthdayDataHelper', false)) {
    final class modSportsmanagementBirthdayDataHelper
    {
        public function getData(Registry $params, Registry $componentParams, CMSApplicationInterface $app): array
        {
            /** @var DatabaseInterface $database */
            $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

            return (new BirthdayHelper())->getData($params, $componentParams, $app, $database);
        }
    }
}

if (!class_exists('modSportsmanagementBirthdayHelper', false)) {
    final class modSportsmanagementBirthdayHelper
    {
        public static function getData(Registry $params): array
        {
            $app = Factory::getApplication();
            /** @var DatabaseInterface $database */
            $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

            return (new BirthdayHelper())->getData(
                $params,
                ComponentHelper::getParams('com_sportsmanagement'),
                $app,
                $database
            );
        }
    }
}
