<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** SportsManagement legacy compatibility bridge for the template table. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TemplateTable;

if (!class_exists(TemplateTable::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TemplateTable.php';
}

if (!class_exists('sportsmanagementTableTemplate', false)) {
    class_alias(TemplateTable::class, 'sportsmanagementTableTemplate');
}
