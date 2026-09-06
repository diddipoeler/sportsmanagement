<?php
/**
 * Native Joomla 5/6 table implementation for prediction administrators.
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

final class PredictionadminTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_admin', 'id', $db);
    }

    public function check()
    {
        if (empty($this->prediction_id) || empty($this->user_id)) {
            $this->setError(Text::_('CHECK FAILED'));

            return false;
        }

        return true;
    }
}
