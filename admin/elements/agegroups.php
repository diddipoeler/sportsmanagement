<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 team age groups field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @deprecated 5.6 Use Diddipoeler\Component\SportsManagement\Administrator\Field\AgegroupsField
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AgegroupsField;

if (!class_exists(AgegroupsField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AgegroupsField.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(AgegroupsField::class)) {
    throw new \RuntimeException('SportsManagement native Agegroups field could not be loaded.', 500);
}

if (!class_exists('JFormFieldagegroups', false)) {
    class_alias(AgegroupsField::class, 'JFormFieldagegroups');
}
