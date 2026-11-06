<?php
/**
 * Base list model for native Joomla 5/6 SportsManagement site models.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\SiteApplication;
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
    private ?int $databaseSelectorOverride = null;

    /** Resolve the active Joomla frontend application through the component service. */
    protected function siteApplication(): SiteApplication
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Read lazy-initialised list state without re-entrant recursion.
     * Returns the current state when a legacy populateState hook calls
     * getState again during Joomla's initialisation.
     * @param string|null $property Optional state property name.
     * @param mixed $default Fallback state value.
     * @return mixed Current model state or requested property.
     */
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

    /**
     * Public access to the already resolved SportsManagement database for view
     * helpers without exposing Joomla's protected BaseDatabaseModel API.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    /**
     * Resolve and verify the native Joomla database connection.
     *
     * @return DatabaseInterface Current Joomla database service.
     * @throws \RuntimeException If Joomla returns an invalid database service.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement site database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Explicitly select the SportsManagement database for list consumers.
     * Rebinds the model to the selected database even when no menu/request
     * context is available, such as in a module or helper.
     * @param int $selector Database selector (zero or one).
     * @return void
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelectorOverride = $selector === 1 ? 1 : 0;
        $this->siteApplication();

        $this->setDatabase(self::joomlaDatabase());
    }

    /**
     * Bind the list model to the selected SportsManagement database.
     * Uses a configured override or the active frontend request selector.
     * @param DatabaseInterface $db Primary Joomla database connection.
     * @return void
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        $selector = $this->databaseSelectorOverride
            ?? ($this->siteApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0);

        parent::setDatabase(SportsManagementDatabaseResolver::resolve($db, $selector));
    }
}
