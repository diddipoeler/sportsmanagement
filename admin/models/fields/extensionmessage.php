<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension-message field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionmessageField;

if (!class_exists(ExtensionmessageField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionmessageField.php';
}

if (!class_exists('JFormFieldextensionmessage', false)) {
    class_alias(ExtensionmessageField::class, 'JFormFieldextensionmessage');
}
