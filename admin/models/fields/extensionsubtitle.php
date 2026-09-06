<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension subtitle field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionsubtitleField;

if (!class_exists(ExtensionsubtitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionsubtitleField.php';
}

if (!class_exists('JFormFieldextensionsubtitle', false)) {
    class_alias(ExtensionsubtitleField::class, 'JFormFieldextensionsubtitle');
}
