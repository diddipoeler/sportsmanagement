<?php
/**
 * Native Joomla 5/6 administrator Club table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

class ClubTable extends SportsManagementTable
{
    /**
     * Initialise the native club table with the selected database service.
     * @param DatabaseInterface $db Active database connection.
     * @param bool $resolved Whether the caller has already resolved the connection.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_club', 'id', $db, $resolved);
    }

    /**
     * Generate the club's URL-safe alias from its name before persistence.
     * @return bool Whether the record passes table validation.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) $this->name);

        return true;
    }
}
