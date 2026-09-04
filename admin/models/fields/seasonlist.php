<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 season list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SeasonlistField;

if (!class_exists(SeasonlistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SeasonlistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(SeasonlistField::class)) {
    throw new \RuntimeException('SportsManagement native Seasonlist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldseasonlist', false)) {
    class_alias(SeasonlistField::class, 'JFormFieldseasonlist');
}
