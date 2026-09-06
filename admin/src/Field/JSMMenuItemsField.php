<?php
/**
 * Joomla 5/6 PSR-4 compatibility shim for the historic JSMMenuItems field type.
 *
 * Joomla resolves the XML field type "JSMMenuItems" to the exact PSR-4 path
 * JSMMenuItemsField.php. The native implementation uses Joomla's normalized
 * class name JsmmenuitemsField, so this shim makes both spellings load safely
 * on case-sensitive file systems.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

require_once __DIR__ . '/JsmmenuitemsField.php';
