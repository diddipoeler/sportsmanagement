<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 matchday list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\MatchdaylistField;

if (!class_exists(MatchdaylistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/MatchdaylistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(MatchdaylistField::class)) {
    throw new \RuntimeException('SportsManagement native Matchdaylist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldMatchdaylist', false)) {
    class_alias(MatchdaylistField::class, 'JFormFieldMatchdaylist');
}
