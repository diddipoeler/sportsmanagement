<?php
/**
 * SportsManagement legacy helper bridge for third-party template overrides.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper;
use Joomla\Registry\Registry;

if (!class_exists(TrainingsDataHelper::class)) {
    require_once __DIR__ . '/src/Helper/TrainingsDataHelper.php';
}

if (!class_exists('modJSMTrainingsData', false)) {
    final class modJSMTrainingsData
    {
        public static function getData($params): array
        {
            $registry = $params instanceof Registry ? $params : new Registry((array) $params);

            return (new TrainingsDataHelper())->getData($registry);
        }
    }
}
