<?php
/** Legacy facade for the Joomla 5/6 birthday module helper. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

if (!class_exists(BirthdayHelper::class)) {
    require_once __DIR__ . '/src/Helper/BirthdayHelper.php';
}

if (!function_exists('jsm_birthday_sort')) {
    function jsm_birthday_sort(array $rows, $arguments = '-', $keys = true): array
    {
        $descendingAge = (string) $arguments === '-';
        usort($rows, static function (array $a, array $b) use ($descendingAge): int {
            $days = ((int) ($a['days_to_birthday'] ?? 0)) <=> ((int) ($b['days_to_birthday'] ?? 0));
            if ($days !== 0) {
                return $days;
            }
            return $descendingAge
                ? ((int) ($b['age'] ?? 0) <=> (int) ($a['age'] ?? 0))
                : ((int) ($a['age'] ?? 0) <=> (int) ($b['age'] ?? 0));
        });
        return $rows;
    }
}

final class modSportsmanagementBirthdayHelper
{
    public static function getData(Registry $params): array
    {
        $app = Factory::getApplication();
        return (new BirthdayHelper())->getData(
            $params,
            ComponentHelper::getParams('com_sportsmanagement'),
            $app
        );
    }
}
