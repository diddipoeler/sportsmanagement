<?php
/** Legacy compatibility bridge for the Joomla 5/6 dependent SQL field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\DependsqlField;

if (!class_exists(DependsqlField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/DependsqlField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(DependsqlField::class) && !class_exists('JFormFieldDependSQL', false)) {
    class_alias(DependsqlField::class, 'JFormFieldDependSQL');
}
