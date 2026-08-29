<?php
/** @package SportsManagement */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;
abstract class SportsManagementListModel extends ListModel
{
    /**
     * Guards Joomla's lazy state initialisation against re-entrant getState() calls
     * from legacy-compatible populateState() implementations.
     */
    private bool $stateReadInProgress = false;

    /**
     * Resolve the active administrator application through Joomla's DI container.
     */
    protected function administratorApplication(): AdministratorApplication
    {
        return Factory::getContainer()->get(AdministratorApplication::class);
    }

    public function getFilterForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        return parent::getFilterForm($data, $loadData);
    }

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
        if (!class_exists('sportsmanagementHelper')) { \JLoader::register('sportsmanagementHelper', JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'); }
        try {
            $sportsManagementDb = \sportsmanagementHelper::getDBConnection();
            if ($sportsManagementDb instanceof DatabaseInterface) { parent::setDatabase($sportsManagementDb); return; }
        } catch (\Throwable) {
        }
        parent::setDatabase($db);
    }
}
