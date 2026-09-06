<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension-version field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionversionField;

if (!class_exists(ExtensionversionField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionversionField.php';
}

if (!class_exists('JFormFieldExtensionVersion', false)) {
    class_alias(ExtensionversionField::class, 'JFormFieldExtensionVersion');
}
