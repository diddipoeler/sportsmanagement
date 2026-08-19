<?php
/** Legacy compatibility bridge for the native extended XML editor model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorModel;

if (!class_exists(SmextxmleditorModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmextxmleditorModel.php';
}

if (!class_exists('sportsmanagementModelsmextxmleditor', false)) {
    class_alias(SmextxmleditorModel::class, 'sportsmanagementModelsmextxmleditor');
}
