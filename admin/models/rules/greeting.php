<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 greeting form rule.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage rules
 * @file       greeting.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Rule\GreetingRule;

if (!class_exists(GreetingRule::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Rule/GreetingRule.php';
}

if (!class_exists(GreetingRule::class)) {
    throw new \RuntimeException('SportsManagement native Greeting rule could not be loaded.', 500);
}

if (!class_exists('JFormRuleGreeting', false)) {
    class_alias(GreetingRule::class, 'JFormRuleGreeting');
}
