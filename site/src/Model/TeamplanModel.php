<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 MVC entry point for the teamplan model.
 *
 * The common project context and the team/division request context are handled
 * natively. The remaining teamplan-specific match queries are delegated
 * temporarily to the historical model and can be migrated method by method
 * without the MVCFactory falling back to site/models/teamplan.php.
 */
final class TeamplanModel extends SportsManagementProjectModel
{
    private int $databaseSelector = 0;
    private int $teamId = 0;
    private int $projectTeamId = 0;
    private int $mode = 0;
    private bool $legacyTeamplanInitialised = false;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        $this->databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->teamId = max(0, $input->getInt('tid', 0));
        $this->projectTeamId = max(0, $input->getInt('ptid', 0));
        $this->mode = max(0, $input->getInt('mode', 0));
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
        return $this->getDivision();
    }

    public function getProjectTeamId(): int
    {
        if ($this->projectId <= 0 || $this->teamId <= 0) {
            $this->projectTeamId = 0;
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('pt.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('t.id') . ' = ' . $this->teamId);

        $db->setQuery($query, 0, 1);
        $this->projectTeamId = (int) $db->loadResult();

        return $this->projectTeamId;
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
        if (!$this->legacyTeamplanInitialised) {
            LegacyBootstrap::bootForView('teamplan');

            $legacyModel = JPATH_SITE . '/components/com_sportsmanagement/models/teamplan.php';
            if (!class_exists('sportsmanagementModelTeamPlan', false) && is_file($legacyModel)) {
                require_once $legacyModel;
            }

            if (!class_exists('sportsmanagementModelTeamPlan', false)) {
                throw new \RuntimeException('Legacy teamplan model bridge could not be loaded.', 500);
            }

            $this->legacyTeamplanInitialised = true;
        }

        $this->synchroniseLegacyTeamplanContext();
    }

    private function synchroniseLegacyTeamplanContext(): void
    {
        if ($this->projectTeamId <= 0 && $this->teamId > 0) {
            $this->getProjectTeamId();
        }

        \sportsmanagementModelTeamPlan::$projectid = $this->projectId;
        \sportsmanagementModelTeamPlan::$teamid = $this->teamId;
        \sportsmanagementModelTeamPlan::$projectteamid = $this->projectTeamId;
        \sportsmanagementModelTeamPlan::$pro_teamid = $this->projectTeamId;
        \sportsmanagementModelTeamPlan::$divisionid = $this->divisionId;
        \sportsmanagementModelTeamPlan::$mode = $this->mode;
        \sportsmanagementModelTeamPlan::$cfg_which_database = $this->databaseSelector;
    }
}
