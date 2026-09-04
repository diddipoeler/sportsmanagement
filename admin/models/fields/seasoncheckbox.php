<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 season checkbox field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SeasoncheckboxField;

if (!class_exists(SeasoncheckboxField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SeasoncheckboxField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(SeasoncheckboxField::class)) {
    throw new \RuntimeException('SportsManagement native Seasoncheckbox field could not be loaded.', 500);
}

if (!class_exists('JFormFieldseasoncheckbox', false)) {
    class_alias(SeasoncheckboxField::class, 'JFormFieldseasoncheckbox');
}
