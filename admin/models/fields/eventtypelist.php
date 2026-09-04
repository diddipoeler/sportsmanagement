<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 event type list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\EventtypelistField;

if (!class_exists(EventtypelistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/EventtypelistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(EventtypelistField::class)) {
    throw new \RuntimeException('SportsManagement native Eventtypelist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldeventtypelist', false)) {
    class_alias(EventtypelistField::class, 'JFormFieldeventtypelist');
}
