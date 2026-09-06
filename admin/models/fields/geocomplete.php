<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 geocomplete field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GeocompleteField;

if (!class_exists(GeocompleteField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GeocompleteField.php';
}

if (!class_exists('JFormFieldGeocomplete', false)) {
    class_alias(GeocompleteField::class, 'JFormFieldGeocomplete');
}
