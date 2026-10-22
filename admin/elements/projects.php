<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 projects field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @deprecated 5.6 Use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectsField
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectsField;

if (!class_exists(ProjectsField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectsField.php',
    ] as $nativeField) {
        if (is_file($nativeField)) {
            require_once $nativeField;
        }
    }
}

if (!class_exists(ProjectsField::class)) {
    throw new \RuntimeException('SportsManagement native Projects field could not be loaded.', 500);
}

if (!class_exists('JFormFieldProjects', false)) {
    class_alias(ProjectsField::class, 'JFormFieldProjects');
}
