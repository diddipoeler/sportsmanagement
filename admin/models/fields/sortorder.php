<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 sort order field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SortorderField;

if (!class_exists(SortorderField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SortorderField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(SortorderField::class)) {
    throw new \RuntimeException('SportsManagement native Sortorder field could not be loaded.', 500);
}

if (!class_exists('JFormFieldsortorder', false)) {
    class_alias(SortorderField::class, 'JFormFieldsortorder');
}
