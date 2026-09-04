<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 playground plan module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;

if (!class_exists(PlaygroundPlanHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/PlaygroundPlanHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(PlaygroundPlanHelper::class)) {
    throw new \RuntimeException('SportsManagement native PlaygroundPlan module helper could not be loaded.', 500);
}

if (!class_exists('modSportsmanagementPlaygroundplanHelper', false)) {
    class_alias(PlaygroundPlanHelper::class, 'modSportsmanagementPlaygroundplanHelper');
}
