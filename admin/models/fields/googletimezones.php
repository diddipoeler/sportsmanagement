<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Google timezones field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GoogletimezonesField;

if (!class_exists(GoogletimezonesField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GoogletimezonesField.php';
}

if (!class_exists('JFormFieldGoogletimezones', false)) {
    class_alias(GoogletimezonesField::class, 'JFormFieldGoogletimezones');
}
