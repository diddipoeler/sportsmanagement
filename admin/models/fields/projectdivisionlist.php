<?php
/**
 * Legacy compatibility bridge for the SportsManagement project-division field.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectdivisionField;

if (!class_exists(ProjectdivisionField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectdivisionField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ProjectdivisionField::class) && !class_exists('JFormFieldprojectdivisionlist', false)) {
    class_alias(ProjectdivisionField::class, 'JFormFieldprojectdivisionlist');
}
