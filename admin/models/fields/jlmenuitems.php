<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JL menu-items field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JlmenuitemsField;

if (!class_exists(JlmenuitemsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JlmenuitemsField.php';
}

if (!class_exists('JFormFieldJLMenuItems', false)) {
    class_alias(JlmenuitemsField::class, 'JFormFieldJLMenuItems');
}
