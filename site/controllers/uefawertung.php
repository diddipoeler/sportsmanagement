<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 UEFA rating controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\UefawertungController;

if (!class_exists(UefawertungController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/UefawertungController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(UefawertungController::class)) {
    throw new \RuntimeException('SportsManagement native Uefawertung controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControlleruefawertung', false)) {
    class_alias(UefawertungController::class, 'sportsmanagementControlleruefawertung');
}
