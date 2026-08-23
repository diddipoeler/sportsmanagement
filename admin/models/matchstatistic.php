<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstatisticModel;

if (!class_exists('sportsmanagementModelMatchstatistic', false)) {
    class_alias(MatchstatisticModel::class, 'sportsmanagementModelMatchstatistic');
}
