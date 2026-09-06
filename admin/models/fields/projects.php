<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 projects field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectsField;

if (!class_exists(ProjectsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectsField.php';
}

if (!class_exists('JFormFieldProjects', false)) {
    class_alias(ProjectsField::class, 'JFormFieldProjects');
}
