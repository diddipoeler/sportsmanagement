<?php
/**
 * SportsManagement Inline Hockey legacy model compatibility bridge.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubLogoService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyClubTeamImportService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyMatchImportService;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Component\ComponentHelper;
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

    /**
     * Import matches and try to refresh their club logos.
     * Uses the explicit or request project ID, resolves API credentials and delegates match and logo synchronization to native services.
     * @param mixed $projectid SportsManagement project ID or zero for input lookup.
     * @param string $username Optional provider username.
     * @param string $password Optional provider password.
     * @return int Imported or updated match count.
     */
    public function getmatches($projectid = 0, $username = '', $password = ''): int
    {
        $input = $this->application()->getInput();
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

    /**
     * Resolve a project's external Inline Hockey match URL.
     * Uses the native project service with the Joomla database to look up the associated provider link.
     * @param mixed $projectid SportsManagement project ID.
     * @return string Matching provider URL.
     */
    public function getMatchLink($projectid): string
    {
        return (new InlineHockeyProjectService($this->database()))->getMatchLink((int) $projectid);
    }

    /**
     * Find or create the project-team association for a season.
     * Delegates association management to the native project service instead of duplicating SQL in the compatibility model.
     * @param mixed $team_id Local team ID.
     * @param mixed $project_id Local project ID.
     * @param mixed $season_id Season ID.
     * @return int Project-team ID.
     */
    public function checkProjectTeam($team_id, $project_id, $season_id): int
    {
        return (new InlineHockeyProjectService($this->database()))->ensureProjectTeam(
            (int) $team_id,
            (int) $project_id,
            (int) $season_id
        );
    }

    /**
     * Import clubs, teams or players according to the selected action.
     * Reads the action from Joomla input, resolves API credentials and forwards the operation to the appropriate native importer.
     * @return int Number of imported or changed records.
     */
    public function getClubs(): int
    {
        $input = $this->application()->getInput();
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

    /**
     * Retain the inactive legacy spreadsheet-processing entry point.
     * This method intentionally performs no work because Excel parsing was removed from the extension.
     * @return void
     */
    public function getdata(): void
    {
        // Excel processing was removed from the extension years ago.
    }

    /**
     * Select authentication credentials for the Inline Hockey importer.
     * Prefers explicitly supplied credentials and otherwise reads the component's stored API username and password.
     * @param string $username Optional username override.
     * @param string $password Optional password override.
     * @return array{0:string,1:string} Effective username and password.
     */
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

    /**
     * Resolve a typed database service for legacy extension callers.
     * Retrieves DatabaseInterface from the current Joomla application dependency injection container.
     * @return DatabaseInterface Joomla database connection.
     */
    private function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $db */
        $db = $this->application()->getContainer()->get(DatabaseInterface::class);

        return $db;
    }

    /**
     * Retrieve and validate the Joomla frontend application.
     * Uses the namespaced application resolver and rejects clients other than the Joomla site application.
     * @return \Joomla\CMS\Application\CMSApplicationInterface Valid site application.
     * @throws \RuntimeException When the active client is not the frontend.
     */
    private function application(): \Joomla\CMS\Application\CMSApplicationInterface
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Inline Hockey legacy model requires the Joomla site application.', 500);
        }

        return $app;
    }
}
