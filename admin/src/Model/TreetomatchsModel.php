<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 list model for tournament-tree match assignments. */
final class TreetomatchsModel extends SportsManagementListModel
{
    private int $nodeId = 0;
    private int $treeId = 0;
    private int $projectId = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'r.roundcode', 'roundcode',
            'mc.match_number', 'match_number',
            'mc.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'r.roundcode', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $this->nodeId = max(0, $input->getInt('nid'));
        $this->treeId = max(0, $input->getInt('tid'));
        $this->projectId = max(0, $input->getInt('pid'));

        if ($this->projectId <= 0) {
            $this->projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        }

        if ($this->treeId <= 0) {
            $this->treeId = (int) $app->getUserState('com_sportsmanagement.tid', 0);
        }

        if ($this->nodeId <= 0) {
            $this->nodeId = (int) $app->getUserState('com_sportsmanagement.nid', 0);
        }

        foreach ([
            'pid' => $this->projectId,
            'tid' => $this->treeId,
            'nid' => $this->nodeId,
        ] as $key => $value) {
            if ($value > 0) {
                $app->setUserState('com_sportsmanagement.' . $key, $value);
            }
        }

        $this->setState('context.project_id', $this->projectId);
        $this->setState('context.tree_id', $this->treeId);
        $this->setState('context.node_id', $this->nodeId);
    }

    /** Replace the complete selected match set for one tree node. */
    public function store(array $data): bool
    {
        $nodeId = (int) ($data['id'] ?? $data['nid'] ?? 0);
        $matchIds = $this->normaliseIds((array) ($data['node_matcheslist'] ?? []));

        if ($nodeId <= 0) {
            $this->setError('Invalid tournament-tree node id.');

            return false;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_treeto_match'))
                ->where($db->quoteName('node_id') . ' = ' . $nodeId);
            $db->setQuery($query)->execute();

            foreach ($matchIds as $matchId) {
                $record = (object) [
                    'node_id' => $nodeId,
                    'match_id' => $matchId,
                ];
                $db->insertObject('#__sportsmanagement_treeto_match', $record);
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /** Return matches that can be assigned to the current tree node. */
    public function getMatches(int $nodeId = 0, int $treeId = 0, int $projectId = 0): array
    {
        $this->getState();
        $nodeId = $nodeId > 0 ? $nodeId : (int) $this->getState('context.node_id', $this->nodeId);
        $treeId = $treeId > 0 ? $treeId : (int) $this->getState('context.tree_id', $this->treeId);
        $projectId = $projectId > 0 ? $projectId : (int) $this->getState('context.project_id', $this->projectId);

        if ($nodeId <= 0 || $treeId <= 0 || $projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $childTeams = $db->getQuery(true)
            ->select($db->quoteName('ttn.team_id'))
            ->from($db->quoteName('#__sportsmanagement_treeto_node', 'ttn'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_treeto_node', 'ttn2')
                . ' ON (' . $db->quoteName('ttn.node') . ' = 2 * ' . $db->quoteName('ttn2.node')
                . ' OR ' . $db->quoteName('ttn.node') . ' = 2 * ' . $db->quoteName('ttn2.node') . ' + 1)'
            )
            ->where($db->quoteName('ttn2.id') . ' = ' . $nodeId)
            ->where($db->quoteName('ttn.treeto_id') . ' = ' . $treeId);

        $query = $this->buildMatchQuery()
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('mc.projectteam1_id') . ' NOT IN (' . $childTeams . ')')
            ->where($db->quoteName('mc.projectteam2_id') . ' NOT IN (' . $childTeams . ')')
            ->order($db->quoteName('r.id') . ' ASC')
            ->order($db->quoteName('mc.id') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getNodeMatches(int $nodeId = 0): array
    {
        $this->getState();
        $nodeId = $nodeId > 0 ? $nodeId : (int) $this->getState('context.node_id', $this->nodeId);

        if ($nodeId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $this->buildMatchQuery()
            ->select([
                $db->quoteName('mc.id', 'notes'),
                $db->quoteName('mc.id', 'info'),
            ])
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_treeto_match', 'ttm')
                . ' ON ' . $db->quoteName('ttm.match_id') . ' = ' . $db->quoteName('mc.id')
            )
            ->where($db->quoteName('ttm.node_id') . ' = ' . $nodeId)
            ->order($db->quoteName('mc.id') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('project_type'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getNode(int $nodeId): ?object
    {
        if ($nodeId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto_node'))
            ->where($db->quoteName('id') . ' = ' . $nodeId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    protected function getListQuery()
    {
        $this->getState();
        $nodeId = (int) $this->getState('context.node_id', $this->nodeId);
        $db = $this->getDatabase();
        $query = $this->buildDisplayQuery()
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_treeto_match', 'ttm')
                . ' ON ' . $db->quoteName('ttm.match_id') . ' = ' . $db->quoteName('mc.id')
            )
            ->where($db->quoteName('ttm.node_id') . ' = ' . $nodeId)
            ->order($db->quoteName('r.roundcode') . ' ASC')
            ->order($db->quoteName('mc.id') . ' ASC');

        return $query;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (int) $this->getState('context.node_id');
        $id .= ':' . (int) $this->getState('context.tree_id');
        $id .= ':' . (int) $this->getState('context.project_id');

        return parent::getStoreId($id);
    }

    private function buildMatchQuery()
    {
        $db = $this->getDatabase();

        return $db->getQuery(true)
            ->select([
                $db->quoteName('mc.id', 'value'),
                "CONCAT(" . $db->quoteName('t1.name') . ", '_vs_', " . $db->quoteName('t2.name')
                    . ", ' [round:', " . $db->quoteName('r.roundcode') . ", ']') AS " . $db->quoteName('text'),
                $db->quoteName('mc.id', 'info'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'mc'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('mc.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('mc.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('mc.round_id'));
    }

    private function buildDisplayQuery()
    {
        $db = $this->getDatabase();

        return $db->getQuery(true)
            ->select([
                $db->quoteName('mc.id', 'mid'),
                $db->quoteName('mc.match_number'),
                $db->quoteName('t1.name', 'projectteam1'),
                $db->quoteName('mc.team1_result', 'projectteam1result'),
                $db->quoteName('mc.team2_result', 'projectteam2result'),
                $db->quoteName('t2.name', 'projectteam2'),
                $db->quoteName('mc.round_id', 'rid'),
                $db->quoteName('mc.published'),
                $db->quoteName('mc.checked_out'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'mc'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('mc.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('mc.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('mc.round_id'));
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
