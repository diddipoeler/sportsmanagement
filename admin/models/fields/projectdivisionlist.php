<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 project-division field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectdivisionField;

if (!class_exists(ProjectdivisionField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectdivisionField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(ProjectdivisionField::class)) {
    throw new \RuntimeException('SportsManagement native Projectdivision field could not be loaded.', 500);
}

if (!class_exists('JFormFieldprojectdivisionlist', false)) {
    class_alias(ProjectdivisionField::class, 'JFormFieldprojectdivisionlist');
}
