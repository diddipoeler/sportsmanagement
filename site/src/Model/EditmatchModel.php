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
    private ?MailerFactoryInterface $injectedMailerFactory = null;

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
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$roundid = $input->getInt('r', 0);
        self::$seasonid = $input->getInt('s', 0);
    }

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
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }
    }

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

    public function savestats($data): bool
    {
        try {
            return $this->writeService()->saveStatistics((array) $data);
        } catch (\Throwable $e) {
            return $this->reportWriteFailure($e);
        }
    }

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

    public function updateRosterBillard($data): bool
    {
        $matchId = (int) ($data['id'] ?? 0);
        $projectId = (int) ($data['project_id'] ?? self::$projectid);
        if ($matchId <= 0 || $projectId <= 0) {
            return false;
        }

        $positions = [];
        foreach ($this->getProjectPositions($projectId) as $position) {
            $positions[(string) $position->name] = (int) $position->id;
        }

        $groups = [
            ['key' => 'roster', 'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER', 'fixed' => null],
            ['key' => 'rosterc', 'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN', 'fixed' => 100],
            ['key' => 'rosterr', 'name' => 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE', 'fixed' => 50],
        ];

        foreach ($groups as $group) {
            $positionId = (int) ($positions[$group['name']] ?? 0);
            if ($positionId <= 0) {
                continue;
            }

            foreach ((array) ($data[$group['key']] ?? []) as $ordering => $teamPlayerId) {
                $teamPlayerId = (int) $teamPlayerId;
                if ($teamPlayerId <= 0) {
                    continue;
                }

                $value = $group['fixed'] === null ? (int) $ordering : (int) $group['fixed'];
                if (!$this->insertMatchPlayer($matchId, $teamPlayerId, $positionId, $value)) {
                    return false;
                }
            }
        }

        return true;
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
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
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

    public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'match') === 0) {
            return new MatchTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

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

    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);
        return empty($data) ? $this->getData() : $data;
    }

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
            'modified' => (new Date())->toSql(),
            'modified_by' => (int) $identity->id,
        ];

        try {
            $this->database()->insertObject('#__sportsmanagement_match_player', $row);
            return true;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return false;
        }
    }

    /** @return array<int,object> */
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

    /** @return array<int,object> */
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

    private function writeService(): MatchWriteService
    {
        return new MatchWriteService($this->database());
    }

    /** @param array<int,array{project_referee_id:int,project_position_id:int}> $assignments */
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
                Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::WARNING, 'jsmerror');
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
            }
        }
    }

    private function reportWriteFailure(\Throwable $e): bool
    {
        Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
        $this->siteApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

        return false;
    }

    private function mailerFactory(): MailerFactoryInterface
    {
        if ($this->injectedMailerFactory !== null) {
            return $this->injectedMailerFactory;
        }

        /** @var MailerFactoryInterface $mailerFactory */
        $mailerFactory = \Joomla\CMS\Factory::getContainer()->get(MailerFactoryInterface::class);

        return $mailerFactory;
    }

    private function database(): DatabaseInterface
    {
        $joomlaDatabase = $this->injectedDatabase ?? self::joomlaDatabase();

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            (int) self::$cfg_which_database
        );
    }

    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }
}
