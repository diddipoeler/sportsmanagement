<?php
/** Legacy compatibility bridge for the Joomla 5/6 ranking columns field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\RankingcolumnsField;

if (!class_exists(RankingcolumnsField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/RankingcolumnsField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(RankingcolumnsField::class) && !class_exists('JFormFieldrankingcolumns', false)) {
    class_alias(RankingcolumnsField::class, 'JFormFieldrankingcolumns');
}
