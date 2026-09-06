<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM link field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmlinkField;

if (!class_exists(JsmlinkField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmlinkField.php';
}

if (!class_exists('JFormFieldJSMLink', false)) {
    class_alias(JsmlinkField::class, 'JFormFieldJSMLink');
}
