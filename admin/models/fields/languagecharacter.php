<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 language character field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\LanguagecharacterField;

if (!class_exists(LanguagecharacterField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LanguagecharacterField.php';
}

if (!class_exists('JFormFieldlanguagecharacter', false)) {
    class_alias(LanguagecharacterField::class, 'JFormFieldlanguagecharacter');
}
