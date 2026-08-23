<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementListModel extends ListModel
{
    /**
     * Keep Joomla's lazy state initialisation re-entrancy safe while legacy list
     * models are migrated to native Joomla 5/6 state handling.
     */
    private bool $stateReadInProgress = false;

    public function getState($property = null, $default = null)
    {
        if ($this->stateReadInProgress) {
            return $property === null ? $this->state : $this->state->get($property, $default);
        }

        $this->stateReadInProgress = true;

        try {
            return parent::getState($property, $default);
        } finally {
            $this->stateReadInProgress = false;
        }
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register(
                'sportsmanagementHelper',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
            );
        }

        try {
            $databaseSelector = Factory::getApplication()->getInput()->getInt('cfg_which_database', 0);
            $sportsManagementDb = \sportsmanagementHelper::getDBConnection(true, $databaseSelector);

            if ($sportsManagementDb instanceof DatabaseInterface) {
                parent::setDatabase($sportsManagementDb);
                return;
            }
        } catch (\Throwable) {
            // Keep Joomla's injected connection as a safe fallback.
        }

        parent::setDatabase($db);
    }
}
