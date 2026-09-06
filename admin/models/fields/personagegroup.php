<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 person age group field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PersonagegroupField;

if (!class_exists(PersonagegroupField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PersonagegroupField.php';
}

if (!class_exists('JFormFieldpersonagegroup', false)) {
    class_alias(PersonagegroupField::class, 'JFormFieldpersonagegroup');
}
