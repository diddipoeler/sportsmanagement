<?php
/** Legacy model bridge for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/UpdsportsmanagementModel.php';

if (!class_exists('sportsmanagementModelUpdsportsmanagement', false)) {
    class_alias(
        \Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel::class,
        'sportsmanagementModelUpdsportsmanagement'
    );
}
