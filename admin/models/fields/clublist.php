<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 club list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ClublistField;

if (!class_exists(ClublistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ClublistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(ClublistField::class)) {
    throw new \RuntimeException('SportsManagement native Clublist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldClublist', false)) {
    class_alias(ClublistField::class, 'JFormFieldClublist');
}
