<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\KunenaCategoryListField;

if (!class_exists(KunenaCategoryListField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/KunenaCategoryListField.php';
}

if (!class_exists('JFormFieldKunenaCategoryList', false)) {
    class_alias(KunenaCategoryListField::class, 'JFormFieldKunenaCategoryList');
}
