<?php
/**
 * Legacy compatibility bridge for the native match staff statistic model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstaffstatisticModel;

if (!class_exists('sportsmanagementModelMatchstaffstatistic', false)) {
    class_alias(MatchstaffstatisticModel::class, 'sportsmanagementModelMatchstaffstatistic');
}
