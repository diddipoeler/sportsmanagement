<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 K2 category list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\Categorylistk2Field;

if (!class_exists(Categorylistk2Field::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/Categorylistk2Field.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(Categorylistk2Field::class)) {
    throw new \RuntimeException('SportsManagement native Categorylistk2 field could not be loaded.', 500);
}

if (!class_exists('JFormFieldcategorylistk2', false)) {
    class_alias(Categorylistk2Field::class, 'JFormFieldcategorylistk2');
}
