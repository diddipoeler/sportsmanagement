<?php
/**
 * Legacy compatibility bridge for the SportsManagement country field.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\CountryField;

if (!class_exists(CountryField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CountryField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(CountryField::class) && !class_exists('JFormFieldCountry', false)) {
    class_alias(CountryField::class, 'JFormFieldCountry');
}
