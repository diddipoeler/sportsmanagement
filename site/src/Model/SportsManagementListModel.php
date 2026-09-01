<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
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

    /** Resolve the active Joomla frontend application. */
    protected function siteApplication(): SiteApplication
    {
        $app = Factory::getApplication();

        if (!$app instanceof SiteApplication) {
            throw new \RuntimeException('SportsManagement site application is unavailable.');
        }

        return $app;
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

    /**
     * Public access to the already resolved SportsManagement database for view
     * helpers without exposing Joomla's protected BaseDatabaseModel API.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    /**
     * Explicitly select the SportsManagement database for compatibility callers
     * which do not derive their context from the current request.
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelectorOverride = $selector === 1 ? 1 : 0;

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $this->setDatabase($joomlaDatabase);
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        $selector = $this->databaseSelectorOverride
            ?? ($this->siteApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0);

        parent::setDatabase(SportsManagementDatabaseResolver::resolve($db, $selector));
    }
}
