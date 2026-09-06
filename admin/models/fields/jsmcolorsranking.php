<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JSM ranking-colors field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmcolorsrankingField;

if (!class_exists(JsmcolorsrankingField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmcolorsrankingField.php';
}

if (!class_exists('JFormFieldjsmcolorsranking', false)) {
    class_alias(JsmcolorsrankingField::class, 'JFormFieldjsmcolorsranking');
}
