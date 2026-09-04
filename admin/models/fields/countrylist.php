<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 country list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CountrylistField;

if (!class_exists(CountrylistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CountrylistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(CountrylistField::class)) {
    throw new \RuntimeException('SportsManagement native Countrylist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldcountrylist', false)) {
    class_alias(CountrylistField::class, 'JFormFieldcountrylist');
}
