<?php
/**
 * SportsManagement legacy table compatibility bridge.
 *
 * @package   SportsManagement
 * @license   GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JsmgcalendarTable;

if (!class_exists('GCalendarTableImport', false)) {
    class_alias(JsmgcalendarTable::class, 'GCalendarTableImport');
}
