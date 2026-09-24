<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension radio-button field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionradiobuttonField;

if (!class_exists(ExtensionradiobuttonField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionradiobuttonField.php';
}

if (!class_exists(ExtensionradiobuttonField::class)) {
    throw new \RuntimeException('SportsManagement native Extensionradiobutton field could not be loaded.', 500);
}

if (!class_exists('JFormFieldExtensionRadioButton', false)) {
    class_alias(ExtensionradiobuttonField::class, 'JFormFieldExtensionRadioButton');
}
