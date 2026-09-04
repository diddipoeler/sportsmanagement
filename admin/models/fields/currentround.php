<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 current round field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CurrentroundField;

if (!class_exists(CurrentroundField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CurrentroundField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(CurrentroundField::class)) {
    throw new \RuntimeException('SportsManagement native Currentround field could not be loaded.', 500);
}

if (!class_exists('JFormFieldCurrentround', false)) {
    class_alias(CurrentroundField::class, 'JFormFieldCurrentround');
}
