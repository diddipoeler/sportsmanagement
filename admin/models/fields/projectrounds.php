<?php
/**
 * Compatibility bridge for the native Joomla 5/6 project rounds field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectroundsField;

if (!class_exists(ProjectroundsField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectroundsField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(ProjectroundsField::class) && !class_exists('JFormFieldprojectrounds', false)) {
    class_alias(ProjectroundsField::class, 'JFormFieldprojectrounds');
}
