<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 associations list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AssociationslistField;

if (!class_exists(AssociationslistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AssociationslistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(AssociationslistField::class)) {
    throw new \RuntimeException('SportsManagement native Associationslist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldAssociationsList', false)) {
    class_alias(AssociationslistField::class, 'JFormFieldAssociationsList');
}
