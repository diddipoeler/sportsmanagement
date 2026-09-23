<?php
/**
 * Legacy table alias for the native Joomla 5/6 MatchSingle table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchSingleTable;

if (!class_exists('sportsmanagementTableMatchSingle', false)) {
    class_alias(MatchSingleTable::class, 'sportsmanagementTableMatchSingle');
}
