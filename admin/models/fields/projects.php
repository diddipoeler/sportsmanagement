<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectsField;

if (!class_exists(ProjectsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectsField.php';
}

if (!class_exists('JFormFieldProjects', false)) {
    class_alias(ProjectsField::class, 'JFormFieldProjects');
}
