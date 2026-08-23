<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 dependent SQL field.
 *
 * Current SportsManagement forms use the namespaced DependsqlField. Keep the
 * historical JFormFieldJLSQL class name available for older overrides without
 * loading the removed Joomla/MooTools behavior and Ajax APIs.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\DependsqlField;

if (!class_exists(DependsqlField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/DependsqlField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(DependsqlField::class) && !class_exists('JFormFieldJLSQL', false)) {
    class_alias(DependsqlField::class, 'JFormFieldJLSQL');
}
