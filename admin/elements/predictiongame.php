<?php
/**
 * Legacy compatibility bridge for the native prediction-game field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictiongameField;

if (!class_exists(PredictiongameField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictiongameField.php';
}

if (!class_exists('JFormFieldPredictiongame', false)) {
    class_alias(PredictiongameField::class, 'JFormFieldPredictiongame');
}
