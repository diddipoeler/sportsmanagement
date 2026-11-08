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
    /**
     * Resolve the native prediction group database table.
     * @param string $type Requested table type.
     * @param string $prefix Legacy table namespace.
     * @param array $config Optional table configuration.
     * @return mixed Prediction group table or parent fallback.
     */
    public function getTable($type = 'predictiongroup', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictiongroup') === 0) {
            return new PredictiongroupTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Announce a successfully inserted prediction group to administrators.
     *
     * Translation pluralisation counts newly created records (exactly one),
     * not the assigned row's primary key. An existing group update does
     * not produce an additional creation notification.
     *
     * @param array $data Persisted prediction group form values.
     * @param int $id Newly saved group identifier, not a record count.
     * @param bool $isNew Whether the save inserted a new prediction group.
     * @return void
     */
    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($isNew) {
            $this->administratorApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', 1),
                'message'
            );
        }
    }
}
