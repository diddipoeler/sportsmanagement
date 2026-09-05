<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 teams field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @deprecated 5.6 Use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamsField
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamsField;

if (!class_exists(TeamsField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamsField.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TeamsField::class)) {
    throw new \RuntimeException('SportsManagement native Teams field could not be loaded.', 500);
}

if (!class_exists('JFormFieldTeams', false)) {
    class_alias(TeamsField::class, 'JFormFieldTeams');
}
