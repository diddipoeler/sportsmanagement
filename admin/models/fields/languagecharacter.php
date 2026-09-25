<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Languagecharacter field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\LanguagecharacterField;

if (!class_exists(LanguagecharacterField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LanguagecharacterField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(LanguagecharacterField::class)) {
    throw new \RuntimeException('SportsManagement native Languagecharacter field could not be loaded.', 500);
}

if (!class_exists('JFormFieldlanguagecharacter', false)) {
    class_alias(LanguagecharacterField::class, 'JFormFieldlanguagecharacter');
}
