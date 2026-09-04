<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 clubs form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ClubsField;

if (!class_exists(ClubsField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ClubsField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(ClubsField::class)) {
    throw new \RuntimeException('SportsManagement native Clubs field could not be loaded.', 500);
}

if (!class_exists('JFormFieldClubs', false)) {
    class_alias(ClubsField::class, 'JFormFieldClubs');
}
