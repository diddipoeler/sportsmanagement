<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RefereeModel;

if (!class_exists('sportsmanagementModelReferee', false)) {
    class_alias(RefereeModel::class, 'sportsmanagementModelReferee');
}
