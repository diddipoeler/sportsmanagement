<?php
/** Legacy helper bridge for the Joomla 5/6 SportsManagement playground plan module. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;

if (!class_exists(PlaygroundPlanHelper::class)) {
    require_once __DIR__ . '/src/Helper/PlaygroundPlanHelper.php';
}

if (!class_exists('modSportsmanagementPlaygroundplanHelper', false)) {
    class_alias(PlaygroundPlanHelper::class, 'modSportsmanagementPlaygroundplanHelper');
}
