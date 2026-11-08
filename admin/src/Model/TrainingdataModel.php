<?php
/**
 * Native Joomla 5/6 administrator model for team training data.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTrainingDataTable;
use Joomla\CMS\Language\Text;

final class TrainingdataModel extends SportsManagementAdminModel
{
    /**
     * Provide the native Joomla table for a team training-data entry.
     *
     * Resolves TeamTrainingData to its namespaced administrator table
     * implementation and delegates unrelated table names to the base model.
     *
     * @param string $type Team training-data or alternate table type.
     * @param string $prefix Legacy Joomla table-class prefix.
     * @param array $config Optional table configuration.
     * @return mixed The resolved Joomla database table.
     */
    public function getTable($type = 'TeamTrainingData', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'TeamTrainingData') === 0) {
            return new TeamTrainingDataTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Notify administrators when a new training-data record was created.
     *
     * The translation's plural parameter must be the number of newly
     * created entries (one), not the generated primary-key identifier.
     * Updates to existing records do not display a creation message.
     *
     * @param array $data Saved training-data fields.
     * @param int $id Primary key of the saved training-data record.
     * @param bool $isNew Whether this save inserted a new record.
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
