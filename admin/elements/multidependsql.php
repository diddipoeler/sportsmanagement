<?php
/** Legacy compatibility bridge for the Joomla 5/6 dependent multi-select field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\MultidependsqlField;

if (!class_exists(MultidependsqlField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/MultidependsqlField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(MultidependsqlField::class) && !class_exists('JFormFieldMultiDependSQL', false)) {
    class_alias(MultidependsqlField::class, 'JFormFieldMultiDependSQL');
}
