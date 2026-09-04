<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 person list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PersonlistField;

if (!class_exists(PersonlistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PersonlistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (!class_exists(PersonlistField::class)) {
    throw new \RuntimeException('SportsManagement native Personlist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldpersonlist', false)) {
    class_alias(PersonlistField::class, 'JFormFieldpersonlist');
}
