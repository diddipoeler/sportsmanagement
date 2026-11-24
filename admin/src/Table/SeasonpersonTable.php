<?php
/**
 * Native Joomla 5/6 table implementation for SportsManagement season-person assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class SeasonpersonTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for person assignments to seasons.
     * Registers the underlying storage table and its primary key with the
     * shared table implementation using the injected database service.
     * @param DatabaseInterface $db Active SportsManagement database driver.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_season_person_id', 'id', $db);
    }
}
