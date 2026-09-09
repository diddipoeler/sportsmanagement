<?php
/**
 * Joomla 5/6 compatibility table for SportsManagement calendar comments.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Legacy table alias retained for existing administrator code.
 */
class sportsmanagementTablejsmgcalendarComment extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__sportsmanagement_gcalendarap_comment', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $parameter = new Registry();
            $parameter->loadArray($array['params']);
            $array['params'] = (string) $parameter;
        }

        return parent::bind($array, $ignore);
    }
}
