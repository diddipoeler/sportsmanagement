<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\Playgroundlist2Field;

if (!class_exists(Playgroundlist2Field::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/Playgroundlist2Field.php';
}

if (!class_exists('JFormFieldplaygroundlist2', false)) {
    class_alias(Playgroundlist2Field::class, 'JFormFieldplaygroundlist2');
}
