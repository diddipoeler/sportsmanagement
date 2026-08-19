<?php
/** Legacy compatibility bridge for the native extended XML editors model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorsModel;

if (!class_exists(SmextxmleditorsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmextxmleditorsModel.php';
}

if (!class_exists('sportsmanagementModelsmextxmleditors', false)) {
    class_alias(SmextxmleditorsModel::class, 'sportsmanagementModelsmextxmleditors');
}
