<?php
/**
 * Native Joomla 5/6 table implementation for prediction results.
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

final class PredictionresultTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_result', 'id', $db);
    }

    public function check()
    {
        if (!(int) ($this->prediction_id ?? 0)
            || !(int) ($this->user_id ?? 0)
            || !(int) ($this->project_id ?? 0)) {
            $this->setError(Text::_('CHECK FAILED'));

            return false;
        }

        return true;
    }
}
