<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 country association field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CountryassociationField;

if (!class_exists(CountryassociationField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CountryassociationField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(CountryassociationField::class)) {
    throw new \RuntimeException('SportsManagement native Countryassociation field could not be loaded.', 500);
}

if (!class_exists('JFormFieldcountryassociation', false)) {
    class_alias(CountryassociationField::class, 'JFormFieldcountryassociation');
}
