<?php
/** Compatibility bridge for the Joomla 5/6 template list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\TemplatelistField;

if (!class_exists(TemplatelistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TemplatelistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(TemplatelistField::class) && !class_exists('JFormFieldtemplatelist', false)) {
    class_alias(TemplatelistField::class, 'JFormFieldtemplatelist');
}
