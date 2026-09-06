<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 sportsmanagement field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SportsmanagementField;

if (!class_exists(SportsmanagementField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsmanagementField.php';
}

if (!class_exists('JFormFieldsportsmanagement', false)) {
    class_alias(SportsmanagementField::class, 'JFormFieldsportsmanagement');
}
