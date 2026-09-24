<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 time form rule.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage rules
 * @file       time.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Rule\TimeRule;

if (!class_exists(TimeRule::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Rule/TimeRule.php';
}

if (!class_exists(TimeRule::class)) {
    throw new \RuntimeException('SportsManagement native Time rule could not be loaded.', 500);
}

if (!class_exists('JFormRuleTime', false)) {
    class_alias(TimeRule::class, 'JFormRuleTime');
}
