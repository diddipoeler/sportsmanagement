<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class PredictionmemberTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_member', 'id', $db);
    }

    public function check()
    {
        if (!(int) ($this->prediction_id ?? 0) || !(int) ($this->user_id ?? 0)) {
            $this->setError(Text::_('CHECK FAILED'));

            return false;
        }

        return true;
    }
}
