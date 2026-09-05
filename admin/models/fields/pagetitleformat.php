<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 page-title format field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PagetitleformatField;

if (!class_exists(PagetitleformatField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PagetitleformatField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(PagetitleformatField::class)) {
    throw new \RuntimeException('SportsManagement native Pagetitleformat field could not be loaded.', 500);
}

if (!class_exists('JFormFieldPageTitleFormat', false)) {
    class_alias(PagetitleformatField::class, 'JFormFieldPageTitleFormat');
}
