<?php
/** Legacy compatibility bridge for the Joomla 5/6 team name format field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamnameformatField;

if (!class_exists(TeamnameformatField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamnameformatField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(TeamnameformatField::class) && !class_exists('JFormFieldTeamNameFormat', false)) {
    class_alias(TeamnameformatField::class, 'JFormFieldTeamNameFormat');
}
