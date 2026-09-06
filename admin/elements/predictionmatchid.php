<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 prediction match field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PredictionmatchidField;

if (!class_exists(PredictionmatchidField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PredictionmatchidField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(PredictionmatchidField::class) && !class_exists('JFormFieldpredictionmatchid', false)) {
    class_alias(PredictionmatchidField::class, 'JFormFieldpredictionmatchid');
}
