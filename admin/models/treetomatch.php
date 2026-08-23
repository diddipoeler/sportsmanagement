<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchModel;

if (!class_exists('sportsmanagementModelTreetomatch', false)) {
    class_alias(TreetomatchModel::class, 'sportsmanagementModelTreetomatch');
}
