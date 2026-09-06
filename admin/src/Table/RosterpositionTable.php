<?php
/**
 * Joomla 5/6 administrator roster-position table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;
\defined('_JEXEC') or die;
use Joomla\Database\DatabaseInterface;
final class RosterpositionTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db) { parent::__construct('#__sportsmanagement_rosterposition', 'id', $db); }
    public function check() { if ((string) $this->short_name !== '') $this->alias = (string) $this->short_name; return parent::check(); }
}
