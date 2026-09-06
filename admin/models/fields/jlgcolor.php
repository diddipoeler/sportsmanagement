<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JLG color field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JlgcolorField;

if (!class_exists(JlgcolorField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JlgcolorField.php';
}

if (!class_exists('JFormFieldJLGColor', false)) {
    class_alias(JlgcolorField::class, 'JFormFieldJLGColor');
}
