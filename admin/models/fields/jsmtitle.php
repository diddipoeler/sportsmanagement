<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM title field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmtitleField;

if (!class_exists(JsmtitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmtitleField.php';
}

if (!class_exists('JFormFieldJSMTitle', false)) {
    class_alias(JsmtitleField::class, 'JFormFieldJSMTitle');
}
