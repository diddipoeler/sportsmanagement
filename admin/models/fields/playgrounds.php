<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 playgrounds field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PlaygroundsField;

if (!class_exists(PlaygroundsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PlaygroundsField.php';
}

if (!class_exists('JFormFieldPlaygrounds', false)) {
    class_alias(PlaygroundsField::class, 'JFormFieldPlaygrounds');
}
