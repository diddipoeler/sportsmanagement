<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 greater-than-zero form rule.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage rules
 * @file       superiorzero.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Rule\SuperiorzeroRule;

if (!class_exists(SuperiorzeroRule::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Rule/SuperiorzeroRule.php';
}

if (!class_exists(SuperiorzeroRule::class)) {
    throw new \RuntimeException('SportsManagement native Superiorzero rule could not be loaded.', 500);
}

if (!class_exists('JFormRuleSuperiorzero', false)) {
    class_alias(SuperiorzeroRule::class, 'JFormRuleSuperiorzero');
}
