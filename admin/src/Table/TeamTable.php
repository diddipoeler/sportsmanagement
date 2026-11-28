<?php
/**
 * Joomla 5/6 table class for SportsManagement teams.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class TeamTable extends SportsManagementTable
{
    /**
     * Initialise the native team table with the selected database service.
     * @param DatabaseInterface $db Active database connection.
     * @param bool $resolved Whether the caller has already resolved the connection.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_team', 'id', $db, $resolved);
    }

    /**
     * Validate the team name, derive missing short names and generate its URL alias.
     * @return bool Whether the record passes table validation.
     */
    public function check()
    {
        $this->name = trim((string) $this->name);

        if ($this->name === '') {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_REQUIRED'));
            return false;
        }

        if (trim((string) $this->middle_name) === '') {
            $parts = preg_split('/\s+/', $this->name) ?: [$this->name];
            $this->middle_name = substr((string) $parts[0], 0, 20);
        }

        if (trim((string) $this->short_name) === '') {
            $parts = preg_split('/\s+/', $this->name) ?: [$this->name];
            $this->short_name = substr((string) $parts[0], 0, 2);
        }

        $this->alias = OutputFilter::stringURLSafe($this->name);

        return parent::check();
    }
}
