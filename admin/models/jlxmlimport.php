<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator XML import model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlxmlimportModel;

if (!class_exists(JlxmlimportModel::class)) {
    $nativeModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlxmlimportModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(JlxmlimportModel::class)) {
    throw new \RuntimeException('SportsManagement native XML import model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelJLXMLImport', false)) {
    class_alias(JlxmlimportModel::class, 'sportsmanagementModelJLXMLImport');
}
