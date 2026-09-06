<?php
/**
 * Joomla 5/6 administrator match table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class MatchTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_match', 'id', $db);
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
