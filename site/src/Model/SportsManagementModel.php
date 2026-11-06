<?php
/**
 * Base model for native Joomla 5/6 SportsManagement site models.
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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementModel extends BaseDatabaseModel
{
    private ?int $databaseSelectorOverride = null;

    /** Resolve the active Joomla frontend application through the shared runtime resolver. */
    protected function siteApplication(): SiteApplication
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Explicitly select the SportsManagement database for non-menu consumers
     * such as modules. Calling this after MVCFactory creation rebinds the model
     * to the requested connection instead of relying on URL input state.
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelectorOverride = $selector === 1 ? 1 : 0;
        $this->siteApplication();

        $this->setDatabase($this->joomlaDatabase());
    }

    /**
     * Expose the resolved SportsManagement database to presentation helpers
     * without leaking Joomla's protected DatabaseAwareTrait API to views.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    /**
     * Resolve the primary Joomla database service for site models.
     * @return DatabaseInterface Valid Joomla database connection.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement site database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Bind this model to the configured SportsManagement connection.
     * Honours an explicit database selection or the current frontend request
     * and delegates database resolution to the native Joomla 5/6 service.
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
