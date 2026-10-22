<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 federations list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\FederationslistField;

if (!class_exists(FederationslistField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/FederationslistField.php',
    ] as $nativeField) {
        if (is_file($nativeField)) {
            require_once $nativeField;
        }
    }
}

if (!class_exists(FederationslistField::class)) {
    throw new \RuntimeException('SportsManagement native Federationslist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldFederationsList', false)) {
    class_alias(FederationslistField::class, 'JFormFieldFederationsList');
}
