<?php
/**
 * Joomla 5/6 administrator position-statistic relation table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class PositionstatisticTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_position_statistic', 'id', $db);
    }

    public function check()
    {
        if (!(int) ($this->position_id ?? 0) || !(int) ($this->statistic_id ?? 0)) {
            $this->setError(Text::_('CHECK FAILED'));

            return false;
        }

        return true;
    }
}
