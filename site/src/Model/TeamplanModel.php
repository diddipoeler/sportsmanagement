<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 MVC entry point for the teamplan model.
 *
 * The common project context is handled by SportsManagementProjectModel.
 * The remaining teamplan-specific match queries are delegated temporarily to
 * the historical model and can now be migrated method by method without the
 * MVCFactory having to fall back to site/models/teamplan.php.
 */
final class TeamplanModel extends SportsManagementProjectModel
{
    private int $databaseSelector = 0;
    private bool $legacyTeamplanInitialised = false;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $this->databaseSelector = Factory::getApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
    }

    public function getPlanRounds(string $ordering = 'ASC'): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelProject::getRounds($ordering, $this->databaseSelector);
    }

    public function getPlanTeams(): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelProject::getTeamsIndexedByPtid(
            0,
            'name',
            $this->databaseSelector
        );
    }

    public function getPlanFavTeams(): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelProject::getFavTeams($this->databaseSelector);
    }

    public function getPlanProjectEvents(): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelProject::getProjectEvents(0, $this->databaseSelector);
    }

    public function getPlanDivision(): ?object
    {
        $this->initialiseLegacyTeamplan();
        $division = \sportsmanagementModelTeamPlan::getDivision();

        return is_object($division) ? $division : null;
    }

    public function getProjectTeamId(): int
    {
        $this->initialiseLegacyTeamplan();

        return (int) \sportsmanagementModelTeamPlan::getProjectTeamId();
    }

    public function getMatches(array $config): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelTeamPlan::getMatches($config);
    }

    public function getMatchesRefering(array $config): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelTeamPlan::getMatchesRefering($config);
    }

    public function getMatchesPerRound(array $config, array $rounds): array
    {
        $this->initialiseLegacyTeamplan();

        return (array) \sportsmanagementModelTeamPlan::getMatchesPerRound($config, $rounds);
    }

    private function initialiseLegacyTeamplan(): void
    {
        if ($this->legacyTeamplanInitialised) {
            return;
        }

        LegacyBootstrap::bootForView('teamplan');

        $legacyModel = JPATH_SITE . '/components/com_sportsmanagement/models/teamplan.php';
        if (!class_exists('sportsmanagementModelTeamPlan', false) && is_file($legacyModel)) {
            require_once $legacyModel;
        }

        if (!class_exists('sportsmanagementModelTeamPlan', false)) {
            throw new \RuntimeException('Legacy teamplan model bridge could not be loaded.', 500);
        }

        // Initialise the historical static request context exactly once. The
        // bridge methods above then preserve the established teamplan queries
        // while Joomla resolves this namespaced model natively.
        new \sportsmanagementModelTeamPlan();
        $this->legacyTeamplanInitialised = true;
    }
}
