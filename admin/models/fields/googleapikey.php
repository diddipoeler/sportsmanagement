<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GoogleapikeyField;

if (!class_exists(GoogleapikeyField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GoogleapikeyField.php';
}

if (!class_exists('JFormFieldGoogleApiKey', false)) {
    class_alias(GoogleapikeyField::class, 'JFormFieldGoogleApiKey');
}
