<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 sports type list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SportstypelistField;

if (!class_exists(SportstypelistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportstypelistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(SportstypelistField::class)) {
    throw new \RuntimeException('SportsManagement native Sportstypelist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldsportstypelist', false)) {
    class_alias(SportstypelistField::class, 'JFormFieldsportstypelist');
}
