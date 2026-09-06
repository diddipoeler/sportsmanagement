<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction games field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictiongamesField;

if (!class_exists(PredictiongamesField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictiongamesField.php';
}

if (!class_exists('JFormFieldPredictiongames', false)) {
    class_alias(PredictiongamesField::class, 'JFormFieldPredictiongames');
}
