<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 avatar provider field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @deprecated 5.6 Use Diddipoeler\Component\SportsManagement\Administrator\Field\AvatarfromcomponentField
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AvatarfromcomponentField;

if (!class_exists(AvatarfromcomponentField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AvatarfromcomponentField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (!class_exists(AvatarfromcomponentField::class)) {
    throw new \RuntimeException('SportsManagement native Avatarfromcomponent field could not be loaded.', 500);
}

if (!class_exists('JFormFieldAvatarFromComponent', false)) {
    class_alias(AvatarfromcomponentField::class, 'JFormFieldAvatarFromComponent');
}
