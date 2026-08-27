<?php
/** Legacy model bridge for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php';
require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/JltournamenttreeModel.php';

if (!class_exists('sportsmanagementModeljltournamenttree', false)) {
    class_alias(
        \Diddipoeler\Component\SportsManagement\Site\Model\JltournamenttreeModel::class,
        'sportsmanagementModeljltournamenttree'
    );
}
