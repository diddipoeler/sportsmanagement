<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 final table rank field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\FinaltablerankField;

if (!class_exists(FinaltablerankField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/FinaltablerankField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(FinaltablerankField::class)) {
    throw new \RuntimeException('SportsManagement native Finaltablerank field could not be loaded.', 500);
}

if (!class_exists('JFormFieldfinaltablerank', false)) {
    class_alias(FinaltablerankField::class, 'JFormFieldfinaltablerank');
}
