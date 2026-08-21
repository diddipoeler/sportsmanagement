<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SywonlinehelpField;

if (!class_exists(SywonlinehelpField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SywonlinehelpField.php';
}

if (!class_exists('JFormFieldSYWOnlineHelp', false)) {
    class_alias(SywonlinehelpField::class, 'JFormFieldSYWOnlineHelp');
}
