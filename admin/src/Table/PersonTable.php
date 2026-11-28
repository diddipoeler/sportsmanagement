<?php
/**
 * Native Joomla 5/6 table implementation for SportsManagement persons.
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

final class PersonTable extends SportsManagementTable
{
    /**
     * Initialise the native person table on the selected database connection.
     * Registers the person storage table and its ID as the primary key.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     * @param bool $resolved Whether the caller has already resolved the connection.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_person', 'id', $db, $resolved);
    }

    /**
     * Validate the person name and generate a URL-safe alias before storage.
     * Requires at least one of the first or last names and normalises an
     * existing alias instead of replacing it with a newly generated one.
     * @return bool Whether the person has a usable name.
     */
    public function check()
    {
        $firstname = trim((string) ($this->firstname ?? ''));
        $lastname = trim((string) ($this->lastname ?? ''));

        if ($firstname === '' && $lastname === '') {
            $this->setError(Text::_('ERROR FIRSTNAME OR LASTNAME REQUIRED'));
            return false;
        }

        $alias = OutputFilter::stringURLSafe(trim($firstname . ' ' . $lastname));
        $this->alias = trim((string) ($this->alias ?? '')) === ''
            ? $alias
            : OutputFilter::stringURLSafe((string) $this->alias);

        return true;
    }
}
