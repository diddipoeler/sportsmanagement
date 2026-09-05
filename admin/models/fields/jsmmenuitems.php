<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM menu items field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmenuitemsField;

if (!class_exists(JsmmenuitemsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmmenuitemsField.php';
}

if (!class_exists('JFormFieldJSMMenuItems', false)) {
    class_alias(JsmmenuitemsField::class, 'JFormFieldJSMMenuItems');
}
