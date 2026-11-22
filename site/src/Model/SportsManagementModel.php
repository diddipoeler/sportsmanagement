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

    /**
     * Resolve the active Joomla site application for model requests.
     *
     * Use the component's shared Joomla 5/6 application resolver instead
     * of a legacy static factory accessor, ensuring input and session state
     * are read from the active site client.
     *
     * @return SiteApplication Active Joomla frontend application.
     * @throws \RuntimeException If the active site application cannot be resolved.
     */
    protected function siteApplication(): SiteApplication
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Set an explicit database connection selection for this site model.
     *
     * Accept only the main or alternate SportsManagement database selector,
     * normalise all other values to the main database, verify the frontend
     * application and rebind the model to the chosen Joomla database service.
     * This is useful when a module creates the model outside a menu request.
     *
     * @param int $selector Requested component database selector (0 or 1).
     * @return void The model database binding is updated.
     * @throws \RuntimeException If the site or database service is unavailable.
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelectorOverride = $selector === 1 ? 1 : 0;
        $this->siteApplication();

        $this->setDatabase($this->joomlaDatabase());
    }

    /**
     * Expose the resolved Joomla database connection to site consumers.
     *
     * Read the database bound to this model using Joomla's native database
     * interface, allowing views and helpers to query component data without
     * depending on the protected DatabaseAwareTrait implementation.
     *
     * @return DatabaseInterface Database currently assigned to this model.
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
