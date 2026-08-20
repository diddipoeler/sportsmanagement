<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportModel;

if (!class_exists('sportsmanagementModeljlextindividualsport', false)) {
    class_alias(JlextindividualsportModel::class, 'sportsmanagementModeljlextindividualsport');
}
