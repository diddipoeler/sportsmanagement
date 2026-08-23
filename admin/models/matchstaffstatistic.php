<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstaffstatisticModel;

if (!class_exists('sportsmanagementModelMatchstaffstatistic', false)) {
    class_alias(MatchstaffstatisticModel::class, 'sportsmanagementModelMatchstaffstatistic');
}
