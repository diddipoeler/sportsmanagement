<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension-link field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionlinkField;

if (!class_exists(ExtensionlinkField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionlinkField.php';
}

if (!class_exists('JFormFieldExtensionLink', false)) {
    class_alias(ExtensionlinkField::class, 'JFormFieldExtensionLink');
}
