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
    public function getTable($type = 'TeamTrainingData', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'TeamTrainingData') === 0) {
            return new TeamTrainingDataTable($this->getDatabase());
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
