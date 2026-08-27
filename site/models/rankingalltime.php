<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RankingalltimeCalculatorModel;

if (!class_exists(RankingalltimeCalculatorModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingalltimeModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingalltimeCalculatorModel.php';
}

if (!class_exists('sportsmanagementModelRankingAllTime', false)) {
    class_alias(RankingalltimeCalculatorModel::class, 'sportsmanagementModelRankingAllTime');
}
