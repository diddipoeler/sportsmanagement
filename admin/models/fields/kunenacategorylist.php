<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Kunena category list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\KunenaCategoryListField;

if (!class_exists(KunenaCategoryListField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/KunenaCategoryListField.php';
}

if (!class_exists('JFormFieldKunenaCategoryList', false)) {
    class_alias(KunenaCategoryListField::class, 'JFormFieldKunenaCategoryList');
}
