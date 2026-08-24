<?php
/**
 * SportsManagement Inline Hockey legacy model compatibility bridge.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubLogoService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubTeamImportService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyMatchImportService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Backward-compatible model name for third-party or legacy extension callers.
 *
 * Active Inline Hockey controllers and plugins use the namespaced services
 * directly. This class intentionally contains no importer implementation.
 */
class sportsmanagementModeljsminlinehockey extends BaseDatabaseModel
{
    public static $success_text = '';
    public $storeFailedColor = 'red';
    public $storeSuccessColor = 'green';
    public $existingInDbColor = 'orange';
    public $success_text_teams = '';
    public $success_text_results = '';
    public $teamart = '';
    public $country = '';
    public $project_type = '';
    public $season_id = 0;
    public $teams = [];
    public $rounds = [];
    public $divisions = [];
    public $matches = [];
    public $projectteams = [];

    public function getmatches($projectid = 0, $username = '', $password = ''): int
    {
        $input = Factory::getApplication()->getInput();
        $projectId = (int) $projectid;

        if ($projectId <= 0) {
            $projectId = $input->post->getInt('projectid', $input->getInt('pid', 0));
        }

        [$username, $password] = $this->credentials((string) $username, (string) $password);
        $matchLink = $input->post->getString('matchlink', '');
        $db = $this->database();
        $api = new InlineHockeyApiClient();
        $projects = new InlineHockeyProjectService($db);
        $changed = (new InlineHockeyMatchImportService($db, $api, $projects))->importMatches(
            $projectId,
            $matchLink,
            $username,
            $password
        );

        try {
            (new InlineHockeyClubLogoService($db, $api, $projects))->syncProjectLogos(
                $projectId,
                $matchLink,
                $username,
                $password
            );
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
        }

        return $changed;
    }

    public function getMatchLink($projectid): string
    {
        return (new InlineHockeyProjectService($this->database()))->getMatchLink((int) $projectid);
    }

    public function checkProjectTeam($team_id, $project_id, $season_id): int
    {
        return (new InlineHockeyProjectService($this->database()))->ensureProjectTeam(
            (int) $team_id,
            (int) $project_id,
            (int) $season_id
        );
    }

    public function getClubs(): int
    {
        $input = Factory::getApplication()->getInput();
        $action = $input->post->getCmd('check', 'clubs');
        [$username, $password] = $this->credentials();
        $service = new InlineHockeyClubTeamImportService(
            $this->database(),
            new InlineHockeyApiClient()
        );

        return match ($action) {
            'teams' => $service->importTeams($username, $password),
            'players' => $service->importPlayers($username, $password),
            default => $service->importClubs($username, $password),
        };
    }

    public function getdata(): void
    {
        // Excel processing was removed from the extension years ago.
    }

    /** @return array{0:string,1:string} */
    private function credentials(string $username = '', string $password = ''): array
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        if ($username === '') {
            $username = (string) $params->get('ishd_benutzername', '');
        }

        if ($password === '') {
            $password = (string) $params->get('ishd_kennwort', '');
        }

        return [$username, $password];
    }

    private function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return $db;
    }
}
