<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 image-select field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ImageSelectField;

if (!class_exists(ImageSelectField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ImageSelectField.php';
}

if (!class_exists('JFormFieldImageSelect', false)) {
    class_alias(ImageSelectField::class, 'JFormFieldImageSelect');
}
