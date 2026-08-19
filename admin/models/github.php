<?php
/** Legacy compatibility bridge for the native administrator Github model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\GithubModel;

if (!class_exists(GithubModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/GithubModel.php';
}

if (!class_exists('sportsmanagementModelgithub', false)) {
    class_alias(GithubModel::class, 'sportsmanagementModelgithub');
}
