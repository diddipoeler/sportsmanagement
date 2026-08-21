<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\DependsqlField;

if (!class_exists(DependsqlField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/DependsqlField.php';
}

if (!class_exists('JFormFieldDependSQL', false)) {
    class_alias(DependsqlField::class, 'JFormFieldDependSQL');
}
