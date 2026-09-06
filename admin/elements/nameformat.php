<?php
/**
 * Legacy compatibility bridge for the SportsManagement name-format element.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\NameformatField;

if (!class_exists(NameformatField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/NameformatField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(NameformatField::class) && !class_exists('JFormFieldNameFormat', false)) {
    class_alias(NameformatField::class, 'JFormFieldNameFormat');
}
