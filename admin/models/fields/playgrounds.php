<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Playgrounds field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PlaygroundsField;

if (!class_exists(PlaygroundsField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PlaygroundsField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(PlaygroundsField::class)) {
    throw new \RuntimeException('SportsManagement native Playgrounds field could not be loaded.', 500);
}

if (!class_exists('JFormFieldPlaygrounds', false)) {
    class_alias(PlaygroundsField::class, 'JFormFieldPlaygrounds');
}
