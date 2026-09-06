<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM subtitle field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmsubtitleField;

if (!class_exists(JsmsubtitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmsubtitleField.php';
}

if (!class_exists('JFormFieldJSMSubtitle', false)) {
    class_alias(JsmsubtitleField::class, 'JFormFieldJSMSubtitle');
}
