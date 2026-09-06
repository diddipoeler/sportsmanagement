<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Google API-key field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GoogleapikeyField;

if (!class_exists(GoogleapikeyField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GoogleapikeyField.php';
}

if (!class_exists('JFormFieldGoogleApiKey', false)) {
    class_alias(GoogleapikeyField::class, 'JFormFieldGoogleApiKey');
}
