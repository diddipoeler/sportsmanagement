<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 table for match staff. */
final class MatchstaffTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_match_staff', 'id', $db);
    }

    public function check()
    {
        if (!($this->match_id && $this->team_staff_id)) {
            $this->setError(Text::_('CHECK FAILED'));

            return false;
        }

        return true;
    }
}
