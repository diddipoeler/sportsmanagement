<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 prediction round field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictionroundidField;

if (!class_exists(PredictionroundidField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictionroundidField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(PredictionroundidField::class) && !class_exists('JFormFieldpredictionroundid', false)) {
    class_alias(PredictionroundidField::class, 'JFormFieldpredictionroundid');
}
