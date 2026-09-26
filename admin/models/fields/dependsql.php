<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 dependent SQL field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\DependsqlField;

if (!class_exists(DependsqlField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/DependsqlField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(DependsqlField::class)) {
    throw new \RuntimeException('SportsManagement native Dependsql field could not be loaded.', 500);
}

if (!class_exists('JFormFieldDependSQL', false)) {
    class_alias(DependsqlField::class, 'JFormFieldDependSQL');
}
