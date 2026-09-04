<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 name-format field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\NameformatField;

if (!class_exists(NameformatField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/NameformatField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(NameformatField::class)) {
    throw new \RuntimeException('SportsManagement native Nameformat field could not be loaded.', 500);
}

if (!class_exists('JFormFieldNameFormat', false)) {
    class_alias(NameformatField::class, 'JFormFieldNameFormat');
}
