<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Google color chooser field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GooglecolorchooserField;

if (!class_exists(GooglecolorchooserField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GooglecolorchooserField.php';
}

if (!class_exists('JFormFieldGoogleColorChooser', false)) {
    class_alias(GooglecolorchooserField::class, 'JFormFieldGoogleColorChooser');
}
