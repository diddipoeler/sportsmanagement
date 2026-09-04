<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 serial number field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SerialnumberField;

if (!class_exists(SerialnumberField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SerialnumberField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(SerialnumberField::class)) {
    throw new \RuntimeException('SportsManagement native Serialnumber field could not be loaded.', 500);
}

if (!class_exists('JFormFieldserialnumber', false)) {
    class_alias(SerialnumberField::class, 'JFormFieldserialnumber');
}
