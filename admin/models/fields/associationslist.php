<?php
/**
 * Compatibility bridge for the Joomla 5/6 associations list field.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\AssociationslistField;

if (!class_exists(AssociationslistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AssociationslistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(AssociationslistField::class) && !class_exists('JFormFieldAssociationsList', false)) {
    class_alias(AssociationslistField::class, 'JFormFieldAssociationsList');
}
