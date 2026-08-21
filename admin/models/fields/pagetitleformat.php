<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PagetitleformatField;

if (!class_exists(PagetitleformatField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PagetitleformatField.php';
}

if (!class_exists('JFormFieldPageTitleFormat', false)) {
    class_alias(PagetitleformatField::class, 'JFormFieldPageTitleFormat');
}
