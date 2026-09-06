<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 user fields selector.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\UserfieldsField;

if (!class_exists(UserfieldsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/UserfieldsField.php';
}

if (!class_exists('JFormFielduserfields', false)) {
    class_alias(UserfieldsField::class, 'JFormFielduserfields');
}
