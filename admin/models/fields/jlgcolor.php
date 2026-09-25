<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Jlgcolor field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JlgcolorField;

if (!class_exists(JlgcolorField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JlgcolorField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(JlgcolorField::class)) {
    throw new \RuntimeException('SportsManagement native Jlgcolor field could not be loaded.', 500);
}

if (!class_exists('JFormFieldJLGColor', false)) {
    class_alias(JlgcolorField::class, 'JFormFieldJLGColor');
}
