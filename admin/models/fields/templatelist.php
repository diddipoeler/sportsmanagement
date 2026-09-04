<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 template list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TemplatelistField;

if (!class_exists(TemplatelistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TemplatelistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(TemplatelistField::class)) {
    throw new \RuntimeException('SportsManagement native Templatelist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldtemplatelist', false)) {
    class_alias(TemplatelistField::class, 'JFormFieldtemplatelist');
}
