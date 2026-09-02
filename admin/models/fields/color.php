<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Color field.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ColorField;

if (!class_exists(ColorField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ColorField.php';
}

if (!class_exists('JFormFieldColor', false)) {
    class_alias(ColorField::class, 'JFormFieldColor');
}
