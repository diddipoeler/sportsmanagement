<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlxmlimport model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlxmlimportModel;

if (!class_exists(JlxmlimportModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlxmlimportModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(JlxmlimportModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlxmlimport model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelJLXMLImport', false)) {
    class_alias(JlxmlimportModel::class, 'sportsmanagementModelJLXMLImport');
}
