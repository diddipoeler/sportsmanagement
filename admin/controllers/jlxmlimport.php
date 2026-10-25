<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 XML import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlimportController;

if (!class_exists(JlxmlimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JlxmlimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Jlxmlimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JlxmlimportController::class)) {
    throw new \RuntimeException('SportsManagement native Jlxmlimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerJLXMLImport', false)) {
    class_alias(JlxmlimportController::class, 'sportsmanagementControllerJLXMLImport');
}
