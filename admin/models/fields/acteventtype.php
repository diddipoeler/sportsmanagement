<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 active event type field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ActeventtypeField;

if (!class_exists(ActeventtypeField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ActeventtypeField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(ActeventtypeField::class)) {
    throw new \RuntimeException('SportsManagement native Acteventtype field could not be loaded.', 500);
}

if (!class_exists('JFormFieldacteventtype', false)) {
    class_alias(ActeventtypeField::class, 'JFormFieldacteventtype');
}
