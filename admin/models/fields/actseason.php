<?php
/**
 * Compatibility bridge for the native Joomla 5/6 active season field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ActseasonField;

if (!class_exists(ActseasonField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ActseasonField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (!class_exists(ActseasonField::class)) {
    throw new \RuntimeException('SportsManagement native Actseason field could not be loaded.', 500);
}

if (!class_exists('JFormFieldactseason', false)) {
    class_alias(ActseasonField::class, 'JFormFieldactseason');
}
