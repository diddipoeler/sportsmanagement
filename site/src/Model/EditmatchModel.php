<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;

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

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);

        $input = Factory::getApplication()->getInput();
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
        $app = Factory::getApplication();
        $identity = $app->getIdentity();
        $row = (object) [
            'match_id' => (int) $match_id,
            'match_number' => (string) $match_numer,
            'projectteam1_id' => (int) $valuehomeprojectteam_id,
            'projectteam2_id' => (int) $valueawayprojectteam_id,
            'teamplayer1_id' => (int) $valuehometeamplayer_id,
            'teamplayer2_id' => (int) $valueawayteamplayer_id,
            'published' => 1,
            'modified' => Factory::getDate()->toSql(),
            'modified_by' => (int) $identity->id,
        ];

        try {
            $this->database()->insertObject('#__sportsmanagement_match_single', $row);
            return true;
        } catch (\Throwable $e) {
            $app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'notice'
            );
            return false;
        }
    }

    public function getSingleMatchData($match_id = 0, $match_number = ''): array
    {
        $db = $this->database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($db->quoteName('m.match_id') . ' = ' . (int) $match_id)
            ->where($db->quoteName('m.match_number') . ' = ' . $db->quote((string) $match_number));
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getSingleMatchDatas($match_id = 0): array
    {
        $db = $this->database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($db->quoteName('m.match_id') . ' = ' . (int) $match_id);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function savestats($data)
    {
        $this->bootLegacyMatchModel();
        return \sportsmanagementModelMatch::savestats($data);
    }

    public function updateReferees($data)
    {
        $this->bootLegacyMatchModel();
        $data['positions'] = \sportsmanagementModelMatch::getProjectPositionsOptions(
            0,
            3,
            (int) ($data['project_id'] ?? 0)
        );
        return $this->legacyMatchModel()->updateReferees($data);
    }

    public function updateStaff($data)
    {
        $this->bootLegacyMatchModel();
        $data['staffpositions'] = \sportsmanagementModelMatch::getProjectPositionsOptions(
            0,
            2,
            (int) ($data['project_id'] ?? 0)
        );
        return $this->legacyMatchModel()->updateStaff($data);
    }

    public function updateRoster($data)
    {
        $this->bootLegacyMatchModel();
        $data['positions'] = \sportsmanagementModelMatch::getProjectPositionsOptions(
            0,
            1,
            (int) ($data['project_id'] ?? 0)
        );
        return $this->legacyMatchModel()->updateRoster($data);
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

    public function updItem($data): bool
    {
        $request = $data['request'] ?? null;

        if (is_array($request)) {
            foreach ($request as $key => $value) {
                $data[$key] = $value;
            }
        } elseif (is_object($request)) {
            $requestData = method_exists($request, 'getArray') ? (array) $request->getArray() : [];
            foreach ($requestData as $key => $value) {
                $data[$key] = $value;
            }
            if (method_exists($request, 'get')) {
                $data['preview'] = $request->get('preview', (string) ($data['preview'] ?? ''), 'raw');
                $data['summary'] = $request->get('summary', (string) ($data['summary'] ?? ''), 'raw');
            }
        }
        unset($data['request']);

        try {
            $table = $this->getTable('match');
            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
            return false;
        }
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
        Form::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');
        Form::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $app = Factory::getApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);
        return empty($data) ? $this->getData() : $data;
    }

    public function getData()
    {
        $input = Factory::getApplication()->getInput();
        $this->_id = $input->getInt('matchid', 0);
        if ($this->_id <= 0) {
            return null;
        }

        $db = $this->database();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('m.id') . ' = ' . $this->_id);
        $db->setQuery($query, 0, 1);
        $this->_data = $db->loadObject() ?: null;
        return $this->_data;
    }

    private function insertMatchPlayer(int $matchId, int $teamPlayerId, int $positionId, int $ordering): bool
    {
        $identity = Factory::getApplication()->getIdentity();
        $row = (object) [
            'match_id' => $matchId,
            'teamplayer_id' => $teamPlayerId,
            'project_position_id' => $positionId,
            'ordering' => $ordering,
            'trikot_number' => $ordering,
            'modified' => Factory::getDate()->toSql(),
            'modified_by' => (int) $identity->id,
        ];

        try {
            $this->database()->insertObject('#__sportsmanagement_match_player', $row);
            return true;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'notice'
            );
            return false;
        }
    }

    /** @return array<int,object> */
    private function getProjectPositions(int $projectId): array
    {
        $db = $this->database();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pos.id'),
                $db->quoteName('pos.name'),
                $db->quoteName('ppos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('pos.ordering') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    private function database(): DatabaseInterface
    {
        $this->bootSportsManagementHelper();

        try {
            $db = \sportsmanagementHelper::getDBConnection(true, (int) self::$cfg_which_database);
            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }

        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    private function bootSportsManagementHelper(): void
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }
    }

    private function bootLegacyMatchModel(): void
    {
        $this->bootSportsManagementHelper();

        if (!class_exists('JSMModelAdmin', false)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/libraries/sportsmanagement/model.php';
        }

        if (!class_exists('sportsmanagementModelMatch', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/match.php';
        }
    }

    private function legacyMatchModel(): object
    {
        $this->bootLegacyMatchModel();
        return new \sportsmanagementModelMatch();
    }
}
