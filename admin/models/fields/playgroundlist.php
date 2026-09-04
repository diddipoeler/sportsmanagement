<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 playground list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PlaygroundlistField;

if (!class_exists(PlaygroundlistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PlaygroundlistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(PlaygroundlistField::class)) {
    throw new \RuntimeException('SportsManagement native Playgroundlist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldplaygroundlist', false)) {
    class_alias(PlaygroundlistField::class, 'JFormFieldplaygroundlist');
}
