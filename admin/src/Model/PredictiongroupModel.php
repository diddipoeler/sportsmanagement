<?php
/**
 * Joomla 5/6 administrator prediction-group form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiongroupTable;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for prediction groups.
 */
final class PredictiongroupModel extends SportsManagementAdminModel
{
    public function getTable($type = 'predictiongroup', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictiongroup') === 0) {
            return new PredictiongroupTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            $this->administratorApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }
    }
}
