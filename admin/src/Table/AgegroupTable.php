<?php
/**
 * Joomla 5/6 administrator agegroup table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class AgegroupTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_agegroup', 'id', $db);
    }

    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) ($this->alias ?: $this->name));

        return true;
    }

    public function load($pk = null, $reset = true)
    {
        if (!parent::load($pk, $reset)) {
            return false;
        }

        $registry = new Registry();
        $registry->loadString((string) $this->extended);
        $this->extended = $registry->toArray();

        return true;
    }
}
