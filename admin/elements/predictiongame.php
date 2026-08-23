<?php
/** Legacy compatibility bridge for the native prediction-game field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictiongameField;

if (!class_exists(PredictiongameField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictiongameField.php';
}

if (!class_exists('JFormFieldPredictiongame', false)) {
    class_alias(PredictiongameField::class, 'JFormFieldPredictiongame');
}
