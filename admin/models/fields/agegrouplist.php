<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 age group list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AgegrouplistField;

if (!class_exists(AgegrouplistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AgegrouplistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(AgegrouplistField::class)) {
    throw new \RuntimeException('SportsManagement native Agegrouplist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldagegrouplist', false)) {
    class_alias(AgegrouplistField::class, 'JFormFieldagegrouplist');
}
