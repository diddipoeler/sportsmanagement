<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editmatch;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchStatsViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchViewDataService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

trait EditmatchStatsViewTrait
{
    public array $playerstats = [];
    public array $staffstats = [];
    public array $stats = [];
    public array $homeStaff = [];
    public array $awayStaff = [];
    public array $staffpositions = [];
    public array $homeRoster = [];
    public array $awayRoster = [];

    private function prepareStatsLayout(EditmatchViewDataService $viewService): void
    {
        if (!$this->match) {
            return;
        }

        $matchId = (int) $this->match->id;
        $statsService = $this->statsViewDataService();
        $teams = $statsService->getMatchTeams($matchId);

        if (!$teams) {
            throw new \RuntimeException('SportsManagement match teams are unavailable.', 404);
        }

        $homeTeamId = (int) $teams->projectteam1_id;
        $awayTeamId = (int) $teams->projectteam2_id;

        $this->teams = $teams;
        $this->positions = array_values($viewService->getProjectPositionsOptions($this->project_id, 1));
        $this->staffpositions = array_values($viewService->getProjectPositionsOptions($this->project_id, 2));
        $this->homeRoster = $viewService->getMatchPersons($homeTeamId, $matchId);
        $this->awayRoster = $viewService->getMatchPersons($awayTeamId, $matchId);
        $this->homeStaff = $statsService->getMatchStaff($homeTeamId, $matchId);
        $this->awayStaff = $statsService->getMatchStaff($awayTeamId, $matchId);
        $this->stats = $statsService->getInputStatistics($this->project_id);

        if ($this->stats === []) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_STATS_POS'), Log::WARNING, 'jsmerror');
        }

        $this->playerstats = $statsService->getMatchStatsInput($matchId, $homeTeamId, $awayTeamId);
        $this->staffstats = $statsService->getMatchStaffStatsInput($matchId, $homeTeamId, $awayTeamId);

        $this->getDocument()->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.editmatch-stats',
            Uri::root() . 'components/com_sportsmanagement/assets/js/editmatch-stats.js'
        );
    }

    private function statsViewDataService(): EditmatchStatsViewDataService
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return new EditmatchStatsViewDataService($database);
    }
}
