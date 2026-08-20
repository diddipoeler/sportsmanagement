<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 table for an individual match row. */
final class MatchSingleTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_match_single', 'id', $db);
    }

    public function check(): bool
    {
        if (!is_numeric($this->team1_result_decision ?? null)) {
            $this->team1_result_decision = null;
        }
        if (!is_numeric($this->team2_result_decision ?? null)) {
            $this->team2_result_decision = null;
        }
        return true;
    }
}
