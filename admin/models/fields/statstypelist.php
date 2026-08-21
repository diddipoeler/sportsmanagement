<?php
/** Compatibility bridge for the Joomla 5/6 statistics type list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\StatstypelistField;

if (!class_exists(StatstypelistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/StatstypelistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(StatstypelistField::class) && !class_exists('JFormFieldStatstypelist', false)) {
    class_alias(StatstypelistField::class, 'JFormFieldStatstypelist');
}
