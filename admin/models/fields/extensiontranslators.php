<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extension-translators field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensiontranslatorsField;

if (!class_exists(ExtensiontranslatorsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensiontranslatorsField.php';
}

if (!class_exists('JFormFieldExtensionTranslators', false)) {
    class_alias(ExtensiontranslatorsField::class, 'JFormFieldExtensionTranslators');
}
