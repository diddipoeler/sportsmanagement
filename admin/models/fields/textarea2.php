<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 textarea field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\Textarea2Field;

if (!class_exists(Textarea2Field::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/Textarea2Field.php';
}

if (!class_exists('JFormFieldTextarea2', false)) {
    class_alias(Textarea2Field::class, 'JFormFieldTextarea2');
}
