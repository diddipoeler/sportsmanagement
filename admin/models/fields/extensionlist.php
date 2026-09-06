<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension-list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionlistField;

if (!class_exists(ExtensionlistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionlistField.php';
}

if (!class_exists('JFormFieldExtensionlist', false)) {
    class_alias(ExtensionlistField::class, 'JFormFieldExtensionlist');
}
