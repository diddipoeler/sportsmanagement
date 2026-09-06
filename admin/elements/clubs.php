<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 clubs field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ClubsField;

if (!class_exists(ClubsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ClubsField.php';
}

if (class_exists(ClubsField::class) && !class_exists('JFormFieldClubs', false)) {
    class_alias(ClubsField::class, 'JFormFieldClubs');
}
