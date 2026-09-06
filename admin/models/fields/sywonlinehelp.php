<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SYW online help field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SywonlinehelpField;

if (!class_exists(SywonlinehelpField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SywonlinehelpField.php';
}

if (!class_exists('JFormFieldSYWOnlineHelp', false)) {
    class_alias(SywonlinehelpField::class, 'JFormFieldSYWOnlineHelp');
}
