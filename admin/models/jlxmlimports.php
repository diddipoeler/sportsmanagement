<?php
/** Legacy compatibility bridge for the native administrator JLXMLImports model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlxmlimportsModel;

if (!class_exists(JlxmlimportsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlxmlimportsModel.php';
}

if (!class_exists('sportsmanagementModelJLXMLImports', false)) {
    class_alias(JlxmlimportsModel::class, 'sportsmanagementModelJLXMLImports');
}
