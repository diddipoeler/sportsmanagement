<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GooglecolorchooserField;

if (!class_exists(GooglecolorchooserField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GooglecolorchooserField.php';
}

if (!class_exists('JFormFieldGoogleColorChooser', false)) {
    class_alias(GooglecolorchooserField::class, 'JFormFieldGoogleColorChooser');
}
