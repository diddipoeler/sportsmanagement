<?php
/**
 * Joomla 5/6 base list model for SportsManagement administrator models.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
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

    /** Resolve the active Joomla administrator application. */
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

    /** Public database contract for administrator views and services. */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        try {
            $app = $this->administratorApplication();
            $selector = $app->getInput()->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            );
            parent::setDatabase((new SportsManagementDatabaseResolver())->resolve($selector, $db));
            return;
        } catch (\Throwable) {
            // List model construction must remain usable when external DB resolution fails.
        }

        parent::setDatabase($db);
    }
}
