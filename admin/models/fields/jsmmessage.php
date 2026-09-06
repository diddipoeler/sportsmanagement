<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM message field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmessageField;

if (!class_exists(JsmmessageField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmmessageField.php';
}

if (!class_exists('JFormFieldJSMMessage', false)) {
    class_alias(JsmmessageField::class, 'JFormFieldJSMMessage');
}
