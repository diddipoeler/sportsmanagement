<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictiongamesField;

if (!class_exists(PredictiongamesField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictiongamesField.php';
}

if (!class_exists('JFormFieldPredictiongames', false)) {
    class_alias(PredictiongamesField::class, 'JFormFieldPredictiongames');
}
