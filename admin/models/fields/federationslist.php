<?php
/**
 * Compatibility bridge for the Joomla 5/6 federations list field.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\FederationslistField;

if (!class_exists(FederationslistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/FederationslistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(FederationslistField::class) && !class_exists('JFormFieldFederationsList', false)) {
    class_alias(FederationslistField::class, 'JFormFieldFederationsList');
}
