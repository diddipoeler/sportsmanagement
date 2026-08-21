<?php
/** Compatibility bridge for the Joomla 5/6 playground list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\PlaygroundlistField;

if (!class_exists(PlaygroundlistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PlaygroundlistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(PlaygroundlistField::class) && !class_exists('JFormFieldplaygroundlist', false)) {
    class_alias(PlaygroundlistField::class, 'JFormFieldplaygroundlist');
}
