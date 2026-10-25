<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SIS import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextsisimportController;

if (!class_exists(JlextsisimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlextsisimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlextsisimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlextsisimportController::class)) {
    throw new \RuntimeException('SportsManagement native Jlextsisimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjlextsisimport', false)) {
    class_alias(JlextsisimportController::class, 'sportsmanagementControllerjlextsisimport');
}
