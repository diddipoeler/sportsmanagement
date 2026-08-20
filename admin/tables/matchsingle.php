<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchSingleTable;

if (!class_exists('sportsmanagementTableMatchSingle', false)) {
    class_alias(MatchSingleTable::class, 'sportsmanagementTableMatchSingle');
}
