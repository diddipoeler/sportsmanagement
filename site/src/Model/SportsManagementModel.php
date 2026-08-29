<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementModel extends BaseDatabaseModel
{
    private ?int $databaseSelectorOverride = null;

    /**
     * Resolve the active frontend application through Joomla's DI container.
     */
    protected function siteApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    /**
     * Explicitly select the SportsManagement database for non-menu consumers
     * such as modules. Calling this after MVCFactory creation rebinds the model
     * to the requested connection instead of relying on URL input state.
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelectorOverride = $selector === 1 ? 1 : 0;

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $this->setDatabase($joomlaDatabase);
    }

    /**
     * Expose the resolved SportsManagement database to presentation helpers
     * without leaking Joomla's protected DatabaseAwareTrait API to views.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        $selector = $this->databaseSelectorOverride
            ?? ($this->siteApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0);

        parent::setDatabase(SportsManagementDatabaseResolver::resolve($db, $selector));
    }
}
