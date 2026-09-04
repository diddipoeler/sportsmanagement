<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 project referee position field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectpositionrefereeField;

if (!class_exists(ProjectpositionrefereeField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectpositionrefereeField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(ProjectpositionrefereeField::class)) {
    throw new \RuntimeException('SportsManagement native Projectpositionreferee field could not be loaded.', 500);
}

if (!class_exists('JFormFieldprojectpositionreferee', false)) {
    class_alias(ProjectpositionrefereeField::class, 'JFormFieldprojectpositionreferee');
}
