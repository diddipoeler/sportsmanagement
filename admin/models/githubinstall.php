<?php
/** Legacy compatibility bridge for the native administrator GitHub installer model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\GithubinstallModel;

if (!class_exists(GithubinstallModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/GithubinstallModel.php';
}

if (!class_exists('sportsmanagementModelgithubinstall', false)) {
    class_alias(GithubinstallModel::class, 'sportsmanagementModelgithubinstall');
}
