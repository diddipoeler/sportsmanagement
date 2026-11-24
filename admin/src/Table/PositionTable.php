<?php
/**
 * Joomla 5/6 administrator position table.
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

final class PositionTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for player and staff position definitions.
     * Registers the storage table and its primary key with the shared table
     * implementation using the injected Joomla database connection.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_position', 'id', $db);
    }

    /**
     * Validate that a position has a nonempty name and generate its URL alias.
     * Reports a translated validation error for missing names and delegates
     * remaining checks to the common table implementation.
     * @return bool Whether the position passes validation.
     */
    public function check()
    {
        if (trim((string) $this->name) === '') {
            $this->setError(Text::_('ERROR NAME REQUIRED'));

            return false;
        }

        $this->alias = OutputFilter::stringURLSafe((string) ($this->alias ?: $this->name));

        return parent::check();
    }
}
