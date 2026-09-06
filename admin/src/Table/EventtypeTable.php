<?php
/**
 * Native Joomla 5/6 administrator Eventtype table.
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

final class EventtypeTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_eventtype', 'id', $db);
    }

    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe($this->alias ?: $this->name);

        return parent::check();
    }
}
