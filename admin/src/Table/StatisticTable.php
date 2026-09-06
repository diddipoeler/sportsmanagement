<?php
/**
 * Joomla 5/6 administrator statistic table.
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
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class StatisticTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_statistic', 'id', $db);
    }

    public function check()
    {
        if (trim((string) $this->name) === '') {
            $this->setError(Text::_('NAME REQUIRED'));

            return false;
        }

        if (trim((string) $this->short) === '') {
            $this->short = strtoupper(substr((string) $this->name, 0, 4));
        }

        $this->alias = OutputFilter::stringURLSafe((string) ($this->alias ?: $this->name));

        return true;
    }
}
