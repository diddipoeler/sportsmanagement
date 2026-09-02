<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 color picker field.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ColorpickerField;

if (!class_exists(ColorpickerField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ColorpickerField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ColorpickerField::class) && !class_exists('JFormFieldColorpicker', false)) {
    class_alias(ColorpickerField::class, 'JFormFieldColorpicker');
}
