<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchSetupService;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 list model for individual-sport match rows. */
final class JlextindividualsportesModel extends ListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? ['mc.id', 'id'];
        $config['dbo'] = $config['dbo'] ?? self::resolveDatabase();
        parent::__construct($config, $factory);
    }

    public function checkGames(object $project, int $matchId, int $roundId, int $projectTeam1Id, int $projectTeam2Id): bool
    {
        return (new IndividualMatchSetupService($this->getDatabase()))->ensureMatchSlots(
            $project,
            $matchId,
            $roundId,
            $projectTeam1Id,
            $projectTeam2Id
        );
    }

    /** @return array<int,object>|false */
    public function getProjectTeams(int $projectId): array|false
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
                $db->quoteName('t.short_name', 'short_name'),
                $db->quoteName('t.notes'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows ?: false;
    }

    /** @return array<int,object> */
    public function getProjectTeamsOptions(int $divisionId = 0): array
    {
        $app = $this->administratorApplication();
        $projectId = (int) $app->getUserState(
            'com_sportsmanagementproject',
            (int) $app->getUserState('com_sportsmanagement.pid', 0)
        );
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                "CASE WHEN CHAR_LENGTH(" . $db->quoteName('t.name') . ") < 25 THEN " . $db->quoteName('t.name') . " ELSE " . $db->quoteName('t.middle_name') . " END AS " . $db->quoteName('text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('text') . ' ASC');

        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . $divisionId);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /** @return array<int,object> */
    public function getMatchesByRound(int $roundId): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('ms') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->where($db->quoteName('ms.round_id') . ' = ' . $roundId);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /** @return array<int,object> */
    public function getPlayer(int $projectTeamId, int $projectId): array
    {
        $app = $this->administratorApplication();
        $seasonId = (int) $app->getUserState('com_sportsmanagement.season_id', 0);
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('tp.id', 'value'),
                "CONCAT(" . $db->quoteName('pl.firstname') . ", ' - ', " . $db->quoteName('pl.nickname') . ", ' - ', " . $db->quoteName('pl.lastname') . ") AS " . $db->quoteName('text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pl.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('tp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pl.published') . ' = 1')
            ->order($db->quoteName('pl.lastname') . ' ASC');

        if ($seasonId > 0) {
            $query->where($db->quoteName('tp.season_id') . ' = ' . $seasonId)
                ->where($db->quoteName('st.season_id') . ' = ' . $seasonId);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getSportType(int $id): string
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query, 0, 1);
        $sportType = (string) ($db->loadResult() ?: '');

        $app = $this->administratorApplication();
        $app->setUserState('com_sportsmanagementsporttype', $sportType);
        $app->setUserState('com_sportsmanagement.sporttype', $sportType);

        if (strtolower($sportType) === 'ringen') {
            $this->_getSinglefile();
        }

        return $sportType;
    }

    public function _getSinglefile(): void
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $matchId = $input->getInt('id', (int) $app->getUserState('com_sportsmanagementmatch_id', 0));
        if ($matchId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('match_number'))
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('id') . ' = ' . $matchId);
        $db->setQuery($query, 0, 1);
        $matchNumber = (string) ($db->loadResult() ?: '');
        if ($matchNumber === '') {
            return;
        }

        $dir = JPATH_SITE . '/tmp/ringerdateien';
        $files = Folder::files($dir, '^MKEinzelkaempfe_Data_' . preg_quote($matchNumber, '/'), false, false, ['^Termine_Schema']);
        $app->enqueueMessage(
            'Einzelkämpfe ' . $matchNumber . ($files ? ' vorhanden' : ' nicht vorhanden'),
            $files ? 'notice' : 'error'
        );
    }

    protected function populateState($ordering = 'mc.id', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = $this->administratorApplication();
        $order = (string) $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'mc.id', 'cmd');
        $dir = strtoupper((string) $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC', 'cmd'));
        $this->setState('list.ordering', in_array($order, ['mc.id', 'id'], true) ? $order : 'mc.id');
        $this->setState('list.direction', $dir === 'DESC' ? 'DESC' : 'ASC');
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $matchId = $this->administratorApplication()->getInput()->getInt('id', 0);
        $query = $db->createQuery()
            ->select($db->quoteName('mc') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'mc'));

        if ($matchId > 0) {
            $query->where($db->quoteName('mc.match_id') . ' = ' . $matchId);
        }

        $ordering = (string) $this->getState('list.ordering', 'mc.id');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order($db->quoteName($ordering === 'id' ? 'mc.id' : 'mc.id') . ' ' . $direction);
        return $query;
    }

    /** Public database contract for the administrator view service. */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    private function administratorApplication(): AdministratorApplication
    {
        return Factory::getContainer()->get(AdministratorApplication::class);
    }

    private static function resolveDatabase(): DatabaseInterface
    {
        $container = Factory::getContainer();
        /** @var AdministratorApplication $app */
        $app = $container->get(AdministratorApplication::class);
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        return (new SportsManagementDatabaseResolver())->resolve(
            $selector,
            $container->get(DatabaseInterface::class)
        );
    }
}
