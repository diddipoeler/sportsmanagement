<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 playground list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\Playgroundlist2Field;

if (!class_exists(Playgroundlist2Field::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/Playgroundlist2Field.php';
}

if (!class_exists('JFormFieldplaygroundlist2', false)) {
    class_alias(Playgroundlist2Field::class, 'JFormFieldplaygroundlist2');
}
