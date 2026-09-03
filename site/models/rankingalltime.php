<?php
/**
 * SportsManagement legacy compatibility bridge for the native all-time ranking model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
