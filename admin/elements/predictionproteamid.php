<?php
/** Legacy compatibility bridge for the Joomla 5/6 prediction project-team field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictionproteamidField;

if (!class_exists(PredictionproteamidField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictionproteamidField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(PredictionproteamidField::class) && !class_exists('JFormFieldpredictionproteamid', false)) {
    class_alias(PredictionproteamidField::class, 'JFormFieldpredictionproteamid');
}
