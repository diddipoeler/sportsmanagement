<?php
/**
 * SportsManagement legacy compatibility adapter.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/MatchreportModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportMatchDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportModel;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

$nativeModelFiles = [
    JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
    JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
    JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportModel.php',
    JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportDataModel.php',
    JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportMatchDataModel.php',
];

foreach ($nativeModelFiles as $nativeModelFile) {
    if (is_file($nativeModelFile)) {
        require_once $nativeModelFile;
    }
}

foreach ([
    MatchreportModel::class,
    MatchreportDataModel::class,
    MatchreportMatchDataModel::class,
] as $requiredModel) {
    if (!class_exists($requiredModel)) {
        throw new \RuntimeException(
            'SportsManagement native Matchreport model stack could not be loaded: ' . $requiredModel,
            500
        );
    }
}

/**
 * Compatibility shell for legacy views and third-party callers.
 */
class sportsmanagementModelMatchReport extends BaseDatabaseModel
{
    public int $matchid = 0;
    public ?object $match = null;
    public int $projectid = 0;
    public ?array $_playersevents = null;
    public ?array $_playersbasicstats = null;
    public ?array $_staffsbasicstats = null;

    private MatchreportModel $nativeModel;
    private MatchreportDataModel $dataModel;
    private MatchreportMatchDataModel $matchDataModel;

    /**
     * Initialise the legacy match-report adapter with native Joomla models.
     * Reads the filtered match and project IDs from the Joomla site request and constructs the native report, auxiliary-data and match-data models used by legacy template overrides.
     *
     * @param array $config Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional native MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);
        $input = $app->getInput();
        $this->matchid = max(0, $input->getInt('mid', 0));
        $this->projectid = max(0, $input->getInt('p', 0));

        $this->nativeModel = new MatchreportModel($config, $factory);
        $this->dataModel = new MatchreportDataModel($config, $factory);
        $this->matchDataModel = new MatchreportMatchDataModel($config, $factory);
    }

    /**
     * Expose the database selection used by the native match-report reader.
     * Delegates the choice to the native model so older callers do not independently determine a SportsManagement connection.
     *
     * @return int Configured SportsManagement database selector.
     */
    public function getDatabaseSelector(): int
    {
        return $this->nativeModel->getDatabaseSelector();
    }

    /**
     * Retrieve the project associated with the selected match report.
     * Passes the request through the native project model rather than duplicating its project lookup and enrichment rules.
     *
     * @return object|null Native project record, if available.
     */
    public function getProject()
    {
        return $this->nativeModel->getProject();
    }

    /**
     * Find a project-team row by its positive assignment identifier.
     * Loads native project teams and compares their projectteamid fields; invalid IDs or missing assignments return null.
     *
     * @param mixed $projectTeamId Requested project-team assignment ID.
     * @return object|null Matching project team or null.
     */
    public function getProjectTeamById($projectTeamId)
    {
        $projectTeamId = (int) $projectTeamId;

        if ($projectTeamId <= 0) {
            return null;
        }

        foreach ($this->nativeModel->getProjectTeams(0) as $team) {
            if ((int) ($team->projectteamid ?? 0) === $projectTeamId) {
                return $team;
            }
        }

        return null;
    }

    /**
     * Retrieve project statistics for the requested statistic and position.
     * Forwards legacy arguments to the native project statistic loader, normalising the position to an integer.
     *
     * @param mixed $statId Statistic ID or collection of IDs.
     * @param mixed $positionId Project position ID.
     * @return array Available project statistic definitions.
     */
    public function getProjectStats($statId = 0, $positionId = 0)
    {
        return $this->nativeModel->getProjectStats($statId, (int) $positionId);
    }

    /**
     * Retrieve a full match report record and retain the current match.
     * Uses the request's match ID unless an override is supplied and caches the native match-data result in the legacy match property.
     *
     * @param mixed $matchId Optional match identifier.
     * @return object|null Match report record or null.
     */
    public function getMatchData($matchId = null)
    {
        $matchId = $matchId === null ? $this->matchid : (int) $matchId;
        return $this->match = $this->matchDataModel->getMatchData($matchId);
    }

    /**
     * Load the written match report and related team names.
     * Delegates to the native match-data reader after converting the legacy match identifier.
     *
     * @param mixed $matchId Match identifier.
     * @return object|null Match text and team names, or null.
     */
    public function getMatchText($matchId)
    {
        return $this->matchDataModel->getMatchText((int) $matchId);
    }

    /**
     * Load the basic record for an individual match.
     * Uses the selected match when no explicit ID is provided and preserves the native read model's row collection.
     *
     * @param mixed $matchId Optional match identifier.
     * @return array Single-match data rows.
     */
    public function getMatchSingleData($matchId = null): array
    {
        return $this->dataModel->getMatchSingleData($matchId === null ? null : (int) $matchId);
    }

    /**
     * List officials assigned to an individual match.
     * Forwards an optional match ID to the native read model and returns its referee records.
     *
     * @param mixed $matchId Optional match identifier.
     * @return array Match referee records.
     */
    public function getMatchReferees($matchId = null): array
    {
        return $this->dataModel->getMatchReferees($matchId === null ? null : (int) $matchId);
    }

    /**
     * Read match commentary in the order expected by legacy templates.
     * Fetches the native chronological commentary list and reverses it to preserve historical newest-first presentation.
     *
     * @param mixed $matchId Optional match identifier.
     * @return array Match commentary rows in reverse order.
     */
    public function getMatchCommentary($matchId = null): array
    {
        return array_reverse(
            $this->dataModel->getMatchCommentary($matchId === null ? null : (int) $matchId)
        );
    }

    /**
     * Retrieve substitutions recorded for a match.
     * Delegates to the native read model, using the current report match unless an ID override is given.
     *
     * @param mixed $matchId Optional match identifier.
     * @return array Substitution records for the match.
     */
    public function getMatchSubstitutions($matchId = null): array
    {
        return $this->dataModel->getMatchSubstitutions($matchId === null ? null : (int) $matchId);
    }

    /**
     * Read match events with the requested comment and ordering options.
     * Converts legacy visibility and descending-sort flags to booleans and delegates event selection to the native model.
     *
     * @param mixed $matchId Optional match identifier.
     * @param mixed $showComments Whether event commentary should be included.
     * @param mixed $sortDescending Whether events should be sorted descending.
     * @return array Filtered match-event records.
     */
    public function getMatchEvents($matchId = null, $showComments = true, $sortDescending = false): array
    {
        return $this->dataModel->getMatchEvents(
            $matchId === null ? null : (int) $matchId,
            (bool) $showComments,
            (bool) $sortDescending
        );
    }

    /**
     * Load details for a match's playing ground.
     * Passes the supplied venue identifier to the native data reader, retaining the legacy method name.
     *
     * @param mixed $playgroundId Playing-ground identifier.
     * @return object|null Venue record when found.
     */
    public function getPlayground($playgroundId)
    {
        return $this->dataModel->getPlayground((int) $playgroundId);
    }

    /**
     * Retrieve a billiards player for a position, team and match.
     * Delegates the position-name and project-team/match filters to the native sport-specific reader.
     *
     * @param mixed $positionName Sport-specific position language key.
     * @param mixed $projectTeamId Project-team assignment identifier.
     * @param mixed $matchId Match identifier.
     * @return mixed Matching billiards player data.
     */
    public function getbillardplayer($positionName = 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN', $projectTeamId = 0, $matchId = 0)
    {
        return $this->nativeModel->getbillardplayer((string) $positionName, (int) $projectTeamId, (int) $matchId);
    }

    /**
     * Run the native check for match-player project-position assignments.
     * Retains the legacy callable entry point while centralising the actual relationship check in the native match-report model.
     *
     * @return void
     */
    public function checkMatchPlayerProjectPositionID(): void
    {
        $this->nativeModel->checkMatchPlayerProjectPositionID();
    }

    /**
     * Retrieve club information associated with a match participant.
     * Normalises the requested club ID and forwards the lookup to the native model.
     *
     * @param mixed $clubId Club identifier.
     * @return object|null Club data when available.
     */
    public function getClubinfo($clubId)
    {
        return $this->nativeModel->getClubinfo((int) $clubId);
    }

    /**
     * Read the selected project's round information.
     * Uses the native match-report round reader to preserve legacy view access to the current round.
     *
     * @return mixed Project round information.
     */
    public function getRound()
    {
        return $this->nativeModel->getRound();
    }

    /**
     * List pictures belonging to a report's configured folder.
     * Delegates picture enumeration to the native reader, ensuring the folder argument is treated as a string.
     *
     * @param mixed $folder Requested match-picture folder.
     * @return array Picture records or filenames.
     */
    public function getMatchPictures($folder)
    {
        return $this->nativeModel->getMatchPictures((string) $folder);
    }

    /**
     * Load match positions for players or staff.
     * Passes the legacy position category to the native model to obtain position definitions.
     *
     * @param mixed $which Position category, normally player.
     * @return array Match position definitions.
     */
    public function getMatchPositions($which = 'player')
    {
        return $this->nativeModel->getMatchPositions((string) $which);
    }

    /**
     * Load match participants for the requested person category.
     * Uses the native reader's player/staff selection while preserving the legacy method signature.
     *
     * @param mixed $which Person category, normally player.
     * @return array Match participant records.
     */
    public function getMatchPersons($which = 'player')
    {
        return $this->nativeModel->getMatchPersons((string) $which);
    }

    /**
     * Retrieve the event types available to match reporting.
     * Forwards the lookup to the native match-report model without reimplementing its event-type query.
     *
     * @return array Match event-type definitions.
     */
    public function getEventTypes()
    {
        return $this->nativeModel->getEventTypes();
    }

    /**
     * Load article information associated with a particular match.
     * Normalises the optional content article, match and category IDs before invoking the native article reader.
     *
     * @param mixed $articleId Joomla content article identifier.
     * @param mixed $matchId Match identifier.
     * @param mixed $categoryId Content category identifier.
     * @return mixed Matching article information.
     */
    public function getMatchArticle($articleId = 0, $matchId = 0, $categoryId = 0)
    {
        return $this->nativeModel->getMatchArticle((int) $articleId, (int) $matchId, (int) $categoryId);
    }

    /**
     * Retrieve calculated match statistics for the report.
     * Keeps legacy templates connected to the native match-statistics provider.
     *
     * @return array Match statistic results.
     */
    public function getMatchStats()
    {
        return $this->nativeModel->getMatchStats();
    }

    /**
     * Load and cache the match players' basic statistics.
     * Saves the native player-statistic results into the historical _playersbasicstats property for legacy renderers.
     *
     * @return array Player statistic records.
     */
    public function getPlayersStats()
    {
        return $this->_playersbasicstats = $this->nativeModel->getPlayersStats();
    }

    /**
     * Load and cache the players' match events.
     * Saves the native event results in the _playersevents property used by existing report templates.
     *
     * @return array Player event records.
     */
    public function getPlayersEvents()
    {
        return $this->_playersevents = $this->nativeModel->getPlayersEvents();
    }

    /**
     * Load and cache staff statistics for this match.
     * Copies the native staff-statistics results into the legacy _staffsbasicstats property.
     *
     * @return array Staff statistic records.
     */
    public function getMatchStaffStats()
    {
        return $this->_staffsbasicstats = $this->nativeModel->getMatchStaffStats();
    }

    /**
     * Resolve the selected playing-ground layout schema.
     * Delegates the supplied schema and display type to the native renderer data model.
     *
     * @param mixed $schema Requested playing-ground schema.
     * @param mixed $which Display type or field category.
     * @return mixed Native playing-ground schema data.
     */
    public function getPlaygroundSchema($schema, $which)
    {
        return $this->nativeModel->getPlaygroundSchema($schema, $which);
    }
}
