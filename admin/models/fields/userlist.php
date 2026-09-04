<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 user list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\UserlistField;

if (!class_exists(UserlistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/UserlistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(UserlistField::class)) {
    throw new \RuntimeException('SportsManagement native Userlist field could not be loaded.', 500);
}

if (!class_exists('JFormFielduserlist', false)) {
    class_alias(UserlistField::class, 'JFormFielduserlist');
}
