<?php
/**
 * Native Joomla 5/6 frontend model for editing matches.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable;
use Diddipoeler\Component\SportsManagement\Site\Service\MatchWriteService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class EditmatchModel extends AdminModel
{
    public const MATCH_ROSTER_STARTER = 0;
    public const MATCH_ROSTER_SUBSTITUTE_IN = 1;
    public const MATCH_ROSTER_SUBSTITUTE_OUT = 2;
    public const MATCH_ROSTER_RESERVE = 3;

    public static $projectid = 0;
    public static $divisionid = 0;
    public static $roundid = 0;
    public static $mode = 0;
    public static $seasonid = 0;
    public static $order = 0;
    public static $cfg_which_database = 0;
    public static $oldlayout = '';

    public $latitude = null;
    public $longitude = null;
    public $name = 'match';

    protected int $_id = 0;
    protected $_data = null;

    private ?DatabaseInterface $injectedDatabase = null;
    private ?DatabaseInterface $authorizedDatabase = null;
    private ?DatabaseInterface $resolvedDatabase = null;
    private ?MailerFactoryInterface $injectedMailerFactory = null;

    /**
     * Initialise the native edit-match form model with optional database and mailer dependencies, then capture the legacy project, round, season, division and layout context from Joomla input.
     * @param array $config Model configuration.
     * @param MVCFactoryInterface|null $factory MVC factory.
     * @param FormFactoryInterface|null $formFactory Form factory.
     * @param DatabaseInterface|null $database Optional database dependency.
     * @param MailerFactoryInterface|null $mailerFactory Optional mailer factory.
     */
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null,
        ?DatabaseInterface $database = null,
        ?MailerFactoryInterface $mailerFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);
        $this->injectedDatabase = $database;
        $this->injectedMailerFactory = $mailerFactory;

        $input = $this->siteApplication()->getInput();
        self::$divisionid = $input->getInt('division', 0);
        self::$mode = $input->getInt('mode', 0);
        self::$order = $input->getInt('order', 0);
        self::$projectid = $input->getInt('p', 0);
        self::$oldlayout = $input->getCmd('oldlayout', '');
        $raw = $input->get('cfg_which_database', 0, 'raw');
        self::$cfg_which_database = $raw === 1 || (is_string($raw) && strlen($raw) <= 64
            && preg_match('/^0*1$/D', $raw) === 1) ? 1 : 0;
        self::$roundid = $input->getInt('r', 0);
        self::$seasonid = $input->getInt('s', 0);
    }

    /**
     * Insert an individual match pairing with its parent match, home and away project teams and players, adding the current identity and UTC modification timestamp.
     * @param mixed $match_id Parent match identifier.
     * @param mixed $match_numer Individual match number.
     * @param mixed $valuehometeamplayer_id Home player identifier.
     * @param mixed $valueawayteamplayer_id Away player identifier.
     * @param mixed $valuehomeprojectteam_id Home project-team identifier.
     * @param mixed $valueawayprojectteam_id Away project-team identifier.
     * @return bool Whether insertion succeeded.
     */
    public function insertSingleMatchData(
        $match_id = 0,
        $match_numer = '',
        $valuehometeamplayer_id = 0,
        $valueawayteamplayer_id = 0,
        $valuehomeprojectteam_id = 0,
        $valueawayprojectteam_id = 0
    ): bool {
        $app = $this->siteApplication();
        $identity = $app->getIdentity();
        $row = (object) [
            'match_id' => (int) $match_id,
            'match_number' => (string) $match_numer,
            'projectteam1_id' => (int) $valuehomeprojectteam_id,
            'projectteam2_id' => (int) $valueawayprojectteam_id,
            'teamplayer1_id' => (int) $valuehometeamplayer_id,
            'teamplayer2_id' => (int) $valueawayteamplayer_id,
            'published' => 1,
            'modified' => (new Date('now', new \DateTimeZone('UTC')))->toSql(),
            'modified_by' => (int) $identity->id,
        ];

        try {
            $this->database()->insertObject('#__sportsmanagement_match_single', $row);
            return true;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }
    }

    /**
     * Load individual match rows for the specified parent match and match number using bound parameters.
     * @param mixed $match_id Parent match identifier.
     * @param mixed $match_number Individual match number.
     * @return array<int,object> Matching individual records.
     */
    public function getSingleMatchData($match_id = 0, $match_number = ''): array
    {
        $matchId = (int) $match_id;
        $matchNumber = (string) $match_number;
        $db = $this->database();
        $query = $db->createQuery()
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($db->quoteName('m.match_id') . ' = :singleMatchId')
            ->where($db->quoteName('m.match_number') . ' = :singleMatchNumber')
            ->bind(':singleMatchId', $matchId, ParameterType::INTEGER)
            ->bind(':singleMatchNumber', $matchNumber, ParameterType::STRING);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Load all individual match records associated with a parent match using a bound identifier.
     * @param mixed $match_id Parent match identifier.
     * @return array<int,object> Individual match rows.
     */
    public function getSingleMatchDatas($match_id = 0): array
    {
        $matchId = (int) $match_id;
        $db = $this->database();
        $query = $db->createQuery()
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($db->quoteName('m.match_id') . ' = :singleMatchesMatchId')
            ->bind(':singleMatchesMatchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Delegate submitted match statistics to the native write service and report a generic frontend error if persistence throws.
     * @param mixed $data Submitted statistics payload.
     * @return bool Whether the write succeeded.
     */
    public function savestats($data): bool
    {
        try {
            return $this->writeService()->saveStatistics((array) $data);
        } catch (\Throwable $e) {
            return $this->reportWriteFailure($e);
        }
    }

    /**
     * Load project referee position options, delegate assignment updates to the write service and notify newly assigned referees after successful persistence.
     * @param mixed $data Submitted match and referee assignments.
     * @return bool Whether the update and notification path succeeded.
     */
    public function updateReferees($data): bool
    {
        $data = (array) $data;
        $data['positions'] = $this->getProjectPositionOptions((int) ($data['project_id'] ?? 0), 3);

        try {
            $result = $this->writeService()->updateReferees($data);
            if (!$result['success']) {
                return false;
            }

            $this->notifyNewRefereeAssignments((int) ($data['id'] ?? 0), $result['added']);
            return true;
        } catch (\Throwable $e) {
            return $this->reportWriteFailure($e);
        }
    }

    /**
     * Load project staff position options and delegate staff assignment changes to the native match write service.
     * @param mixed $data Submitted match staff payload.
     * @return bool Whether the update succeeded.
     */
    public function updateStaff($data): bool
    {
        $data = (array) $data;
        $data['staffpositions'] = $this->getProjectPositionOptions((int) ($data['project_id'] ?? 0), 2);

        try {
            return $this->writeService()->updateStaff($data);
        } catch (\Throwable $e) {
            return $this->reportWriteFailure($e);
        }
    }

    /**
     * Load project player position options and delegate roster updates to the native match write service.
     * @param mixed $data Submitted match roster payload.
     * @return bool Whether the update succeeded.
     */
    public function updateRoster($data): bool
    {
        $data = (array) $data;
        $data['positions'] = $this->getProjectPositionOptions((int) ($data['project_id'] ?? 0), 1);

        try {
            return $this->writeService()->updateRoster($data);
        } catch (\Throwable $e) {
            return $this->reportWriteFailure($e);
        }
    }

    /**
     * Persist billiards roster entries only for an existing match in the
     * requested project, rejecting malformed IDs before inserting anything.
     */
    public function updateRosterBillard($data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        $matchId = self::positiveId($data['id'] ?? null);
        $projectId = self::positiveId($data['project_id'] ?? self::$projectid);

        if ($matchId <= 0 || $projectId <= 0) {
            return false;
        }

        $groups = [
            ['key' => 'roster', 'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER', 'fixed' => null],
            ['key' => 'rosterc', 'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN', 'fixed' => 100],
            ['key' => 'rosterr', 'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE', 'fixed' => 50],
        ];
        $roster = [];

        foreach ($groups as $group) {
            $selected = $data[$group['key']] ?? [];

            if (!is_array($selected)) {
                return false;
            }

            foreach ($selected as $ordering => $player) {
                // Zero is the legacy "not selected" placeholder.
                if ($player === null || $player === '' || $player === 0 || $player === '0') {
                    continue;
                }

                $playerId = self::positiveId($player);

                if ($playerId <= 0) {
                    return false;
                }

                if ($group['fixed'] === null
                    && (!is_int($ordering) && !is_string($ordering)
                        || !ctype_digit((string) $ordering))) {
                    return false;
                }

                $roster[] = [
                    'player_id' => $playerId,
                    'position' => $group['name'],
                    'ordering' => $group['fixed'] ?? (int) $ordering,
                ];
            }
        }

        try {
            $db = $this->database();
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_round', 'r')
                    . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
                )
                ->where($db->quoteName('m.id') . ' = :billardMatchId')
                ->where($db->quoteName('r.project_id') . ' = :billardProjectId')
                ->bind(':billardMatchId', $matchId, ParameterType::INTEGER)
                ->bind(':billardProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);

            if ((int) $db->loadResult() !== 1) {
                return false;
            }

            $positions = [];

            foreach ($this->getProjectPositions($projectId) as $position) {
                $positions[(string) $position->name] = (int) $position->id;
            }

            // Resolve every selected role before the first write.
            foreach ($roster as &$item) {
                $item['position_id'] = $positions[$item['position']] ?? 0;

                if ($item['position_id'] <= 0) {
                    return false;
                }
            }
            unset($item);

            foreach ($roster as $item) {
                if (!$this->insertMatchPlayer(
                    $matchId,
                    $item['player_id'],
                    $item['position_id'],
                    $item['ordering']
                )) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            return $this->reportWriteFailure($e);
        }
    }

    /**
     * Store a frontend match form without allowing nested request fields to
     * redirect the write to another match or to create a new record.
     */
    public function updItem($data): bool
    {
        $data = (array) $data;
        $matchId = self::positiveId($data['id'] ?? null);

        if ($matchId <= 0) {
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
            return false;
        }

        $request = $data['request'] ?? null;
        $requestData = is_array($request) ? $request : [];

        if (is_object($request) && method_exists($request, 'getArray')) {
            $requestData = (array) $request->getArray();

            if (method_exists($request, 'get')) {
                $requestData['preview'] = $request->get(
                    'preview', (string) ($data['preview'] ?? ''), 'raw'
                );
                $requestData['summary'] = $request->get(
                    'summary', (string) ($data['summary'] ?? ''), 'raw'
                );
            }
        }

        $formData = is_array($data['jform'] ?? null) ? $data['jform'] : [];

        unset($data['request'], $data['jform']);
        unset($requestData['id'], $formData['id']);

        // Joomla uses jform, while legacy editors use the request array.
        // Preserve explicit zeros/cleared values and the top-level match ID.
        $data = array_replace($data, $requestData, $formData);
        $data['id'] = $matchId;

        try {
            $table = $this->getTable('match');

            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

            return false;
        }
    }

    /** Only positive decimal IDs within PHP integer range are record keys. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $value = trim((string) $value);

        if ($value === '' || !ctype_digit($value)) {
            return 0;
        }

        $digits = ltrim($value, '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    /**
     * Instantiate the native match table with the active model database for the historical match table name, otherwise defer to Joomla's table factory.
     * @param string $type Requested table type.
     * @param string $prefix Legacy table namespace.
     * @param array $config Table configuration.
     * @return mixed Match table or parent factory result.
     */
    public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'match') === 0) {
            return new MatchTable($this->database());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Register SportsManagement administrator form and legacy field paths, then load the named frontend edit form with optional persisted values.
     * @param array $data Existing form values.
     * @param bool $loadData Whether to load model data.
     * @return mixed Joomla form or false.
     */
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/fields');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }

    /**
     * Restore pending edit-match form data from the Joomla session, falling back to the stored match when no pending values exist.
     * @return mixed Pending values or match record.
     */
    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);
        return empty($data) ? $this->getData() : $data;
    }

    /**
     * Load the requested match and both team names through the project-team and season-team relations, caching the selected record in the model.
     * @return object|null Match record or null.
     */
    public function getData()
    {
        $input = $this->siteApplication()->getInput();
        $this->_id = $input->getInt('matchid', 0);
        if ($this->_id <= 0) {
            return null;
        }

        $db = $this->database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m') . '.*',
                $db->quoteName('t1.name', 'hometeam'),
                $db->quoteName('t2.name', 'awayteam'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('m.id') . ' = :editMatchId');
        $matchId = (int) $this->_id;
        $query->bind(':editMatchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $this->_data = $db->loadObject() ?: null;
        return $this->_data;
    }

    /**
     * Insert a match roster row for a player and project position, preserving ordering as the shirt number and recording the current user's modification metadata.
     * @param int $matchId Match identifier.
     * @param int $teamPlayerId Player assignment identifier.
     * @param int $positionId Project position identifier.
     * @param int $ordering Roster order and shirt number.
     * @return bool Whether insertion succeeded.
     */
    private function insertMatchPlayer(int $matchId, int $teamPlayerId, int $positionId, int $ordering): bool
    {
        $app = $this->siteApplication();
        $identity = $app->getIdentity();
        $row = (object) [
            'match_id' => $matchId,
            'teamplayer_id' => $teamPlayerId,
            'project_position_id' => $positionId,
            'ordering' => $ordering,
            'trikot_number' => $ordering,
            'modified' => (new Date('now', new \DateTimeZone('UTC')))->toSql(),
            'modified_by' => (int) $identity->id,
        ];

        try {
            $this->database()->insertObject('#__sportsmanagement_match_player', $row);
            return true;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }
    }

    /**
     * Load the positions assigned to a project in the configured position order.
     * Includes both the position identifier and the project-specific assignment
     * identifier needed when persisting match rosters.
     * @param int $projectId Project identifier.
     * @return array<int,object> Project position records.
     */
    private function getProjectPositions(int $projectId): array
    {
        $db = $this->database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pos.id'),
                $db->quoteName('pos.name'),
                $db->quoteName('ppos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('ppos.project_id') . ' = :projectPositionsProjectId')
            ->order($db->quoteName('pos.ordering') . ' ASC')
            ->bind(':projectPositionsProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Retrieve project position options for a specific person type.
     * Returns records keyed by the project-position ID with the option label
     * and underlying position identifier for roster and assignment updates.
     * @param int $projectId Project identifier.
     * @param int $personType Player, staff or referee person type.
     * @return array<int,object> Position options keyed by assignment identifier.
     */
    private function getProjectPositionOptions(int $projectId, int $personType): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('pos.id', 'posid'),
                $db->quoteName('ppos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->where($db->quoteName('ppos.project_id') . ' = :positionOptionsProjectId')
            ->where($db->quoteName('pos.persontype') . ' = :positionOptionsPersonType')
            ->order($db->quoteName('pos.ordering') . ' ASC')
            ->bind(':positionOptionsProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':positionOptionsPersonType', $personType, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList('value') ?: [];
    }

    /**
     * Create the native match write service using the effective component database connection.
     * @return MatchWriteService Service for match updates.
     */
    private function writeService(): MatchWriteService
    {
        return new MatchWriteService($this->database());
    }

    /**
     * Notify newly assigned referees using the configured email template.
     * Retrieves match, venue, team and recipient details for each assignment,
     * skips missing recipients and reports mail failures without exposing
     * exception details to the frontend.
     * @param int $matchId Match identifier.
     * @param array<int,array{project_referee_id:int,project_position_id:int}> $assignments Newly persisted referee assignments.
     * @return void
     */
    private function notifyNewRefereeAssignments(int $matchId, array $assignments): void
    {
        $template = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ishd_referee_insert_match_mail', '');
        if ($matchId <= 0 || !$assignments || trim($template) === '') {
            return;
        }

        $app = $this->siteApplication();
        $db = $this->database();

        foreach ($assignments as $assignment) {
            $projectRefereeId = (int) ($assignment['project_referee_id'] ?? 0);
            if ($projectRefereeId <= 0) {
                continue;
            }

            $query = $db->createQuery()
                ->select([
                    $db->quoteName('person.firstname'),
                    $db->quoteName('person.lastname'),
                    $db->quoteName('person.email'),
                    $db->quoteName('m.match_date'),
                    $db->quoteName('m.match_timestamp'),
                    $db->quoteName('pg.name', 'playground_name'),
                    $db->quoteName('t1.name', 'team1'),
                    $db->quoteName('t2.name', 'team2'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'pg') . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('m.playground_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_match_referee', 'mr') . ' ON ' . $db->quoteName('mr.match_id') . ' = ' . $db->quoteName('m.id') . ' AND ' . $db->quoteName('mr.project_referee_id') . ' = :mailProjectRefereeId')
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pref.person_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'person') . ' ON ' . $db->quoteName('person.id') . ' = ' . $db->quoteName('spi.person_id'))
                ->where($db->quoteName('m.id') . ' = :mailMatchId')
                ->bind(':mailProjectRefereeId', $projectRefereeId, ParameterType::INTEGER)
                ->bind(':mailMatchId', $matchId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);
            $context = $db->loadObject();

            if (!$context || trim((string) $context->email) === '') {
                continue;
            }

            $timestamp = (int) ($context->match_timestamp ?? 0);
            if ($timestamp <= 0) {
                $timestamp = strtotime((string) ($context->match_date ?? '')) ?: 0;
            }
            $when = $timestamp > 0 ? date('d.m.Y - H:i', $timestamp) : (string) ($context->match_date ?? '');

            $body = sprintf(
                $template,
                (string) $context->firstname,
                (string) $context->lastname,
                'Schiedsrichterverein',
                'Schiedsrichterstufe',
                $when,
                (string) ($context->playground_name ?? ''),
                'Ligakurzname',
                (string) ($context->team1 ?? ''),
                (string) ($context->team2 ?? '')
            );

            try {
                $mailer = $this->mailerFactory()->createMailer();
                $mailFrom = (string) $app->get('mailfrom', '');
                $fromName = (string) $app->get('fromname', '');
                if ($mailFrom !== '') {
                    $mailer->setSender([$mailFrom, $fromName]);
                }
                $mailer->addRecipient((string) $context->email);
                $mailer->setSubject('Neueinteilung Schiedsrichtereinsatz am : ' . $when);
                $mailer->isHTML(true);
                $mailer->setBody($body);
                $mailer->send();
            } catch (\Throwable $e) {
                Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
            }
        }
    }

    /**
     * Log a write exception server-side and enqueue a generic translated frontend error, returning false to the caller.
     * @param \Throwable $e Write failure.
     * @return bool Always false.
     */
    private function reportWriteFailure(\Throwable $e): bool
    {
        Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
        $this->siteApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

        return false;
    }

    /**
     * Return the injected mailer factory when supplied or resolve Joomla's mailer service from the DI container.
     * @return MailerFactoryInterface Active mailer factory.
     */
    private function mailerFactory(): MailerFactoryInterface
    {
        if ($this->injectedMailerFactory !== null) {
            return $this->injectedMailerFactory;
        }

        /** @var MailerFactoryInterface $mailerFactory */
        $mailerFactory = \Joomla\CMS\Factory::getContainer()->get(MailerFactoryInterface::class);

        return $mailerFactory;
    }

    /**
     * Resolve and retain the selected component database for this model.
     * Explicitly authorized write connections take precedence over request
     * selection, preventing a subsequent resolution from changing targets.
     * @return DatabaseInterface Effective connection.
     */
    private function database(): DatabaseInterface
    {
        if ($this->authorizedDatabase !== null) {
            return $this->authorizedDatabase;
        }
        if ($this->resolvedDatabase !== null) {
            return $this->resolvedDatabase;
        }

        if ($this->injectedDatabase === null) {
            $joomlaDatabase = self::joomlaDatabase();
        } else {
            $joomlaDatabase = $this->injectedDatabase;
        }

        return $this->resolvedDatabase = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            (int) self::$cfg_which_database
        );
    }

    /**
     * Bind a connection already verified by the controller's match ACL.
     * This is the effective connection, not a primary fallback to resolve
     * again. Both native tables and relation write services use it.
     * @param DatabaseInterface $database Authorized effective connection.
     * @return void
     */
    public function useAuthorizedDatabase(DatabaseInterface $database): void
    {
        $this->authorizedDatabase = $database;
    }

    /**
     * Expose the effective model database for the read-only match view.
     * Reuses the same connection as the model's match record lookup.
     * @return DatabaseInterface Effective SportsManagement database.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->database();
    }

    /**
     * Retrieve the primary Joomla database service through dependency injection.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    /**
     * Resolve the active site application using the shared SportsManagement runtime service.
     * @return CMSApplicationInterface Active frontend application.
     */
    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }
}
