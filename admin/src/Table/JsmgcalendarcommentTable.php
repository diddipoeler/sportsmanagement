<?php
/**
 * Native Joomla 5/6 table for SportsManagement Google Calendar comments.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class JsmgcalendarcommentTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_gcalendarap_comment', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $params = new Registry();
            $params->loadArray($array['params']);
            $array['params'] = $params->toString();
        }

        return parent::bind($array, $ignore);
    }
}
