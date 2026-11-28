<?php
/**
 * Joomla 5/6 administrator quote table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class SmquoteTable extends SportsManagementTable
{
    /**
     * Initialise the quote table using the selected SportsManagement database.
     * Registers the quote storage table and its primary key, preserving an
     * already resolved connection when supplied by the calling model.
     * @param DatabaseInterface $db Active database connection.
     * @param bool $resolved Whether the connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_rquote', 'id', $db, $resolved);
    }
}
