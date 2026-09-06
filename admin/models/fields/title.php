<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 title field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TitleField;

if (!class_exists(TitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TitleField.php';
}

if (!class_exists('JFormFieldTitle', false)) {
    class_alias(TitleField::class, 'JFormFieldTitle');
}
