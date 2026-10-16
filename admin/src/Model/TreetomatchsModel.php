<?php
/**
 * Native Joomla 5/6 list model for tournament-tree match assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 list model for tournament-tree match assignments. */
final class TreetomatchsModel extends SportsManagementListModel
{
    private const MAX_NODE_MATCHES = 500;

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

        $app = $this->administratorApplication();
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

    /** Replace one node's match set only after checking tree and project scope. */
    public function store(array $data): bool
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorized to change tournament-tree matches.', 403);
        }

        $nodeId = $this->strictPositiveId($data['id'] ?? $data['nid'] ?? null);
        $treeId = $this->strictPositiveId($data['tid'] ?? null);
        $projectId = $this->strictPositiveId($data['pid'] ?? null);
        $rawMatches = $data['node_matcheslist'] ?? [];

        if ($nodeId <= 0 || $treeId <= 0 || $projectId <= 0
            || !is_array($rawMatches) || count($rawMatches) > self::MAX_NODE_MATCHES) {
            $this->setError('Invalid tournament-tree assignment.');
            return false;
        }

        try {
            $matchIds = $this->normaliseIds($rawMatches);
        } catch (\UnexpectedValueException) {
            $this->setError('Invalid tournament-tree match selection.');
            return false;
        }

        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            // The selected node must belong to the submitted tree and project.
            $nodeQuery = $db->createQuery()
                ->select($db->quoteName('n.id'))
                ->from($db->quoteName('#__sportsmanagement_treeto_node', 'n'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_treeto', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('n.treeto_id')
                )
                ->where($db->quoteName('n.id') . ' = :assignmentNodeId')
                ->where($db->quoteName('n.treeto_id') . ' = :assignmentTreeId')
                ->where($db->quoteName('t.project_id') . ' = :assignmentProjectId')
                ->bind(':assignmentNodeId', $nodeId, ParameterType::INTEGER)
                ->bind(':assignmentTreeId', $treeId, ParameterType::INTEGER)
                ->bind(':assignmentProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($nodeQuery, 0, 1);

            if ((int) $db->loadResult() !== $nodeId) {
                $this->setError('Tournament-tree node does not belong to the selected project.');
                return false;
            }

            if ($matchIds !== []) {
                // Every assigned match must belong to the same project as
                // the tree. Reject mixed/foreign sets instead of dropping IDs.
                $matchQuery = $db->createQuery()
                    ->select($db->quoteName('m.id'))
                    ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__sportsmanagement_round', 'r')
                        . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
                    )
                    ->whereIn($db->quoteName('m.id'), $matchIds, ParameterType::INTEGER)
                    ->where($db->quoteName('r.project_id') . ' = :assignmentMatchProjectId')
                    ->bind(':assignmentMatchProjectId', $projectId, ParameterType::INTEGER);
                $db->setQuery($matchQuery, 0, self::MAX_NODE_MATCHES);
                $allowed = array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));
                sort($allowed);
                $expected = $matchIds;
                sort($expected);

                if ($allowed !== $expected) {
                    $this->setError('Selected matches do not belong to this project.');
                    return false;
                }
            }

            // Validation is complete: only now may the existing set be erased.
            $db->transactionStart();
            $transactionStarted = true;

            $query = $db->createQuery()
                ->delete($db->quoteName('#__sportsmanagement_treeto_match'))
                ->where($db->quoteName('node_id') . ' = :storeNodeId')
                ->bind(':storeNodeId', $nodeId, ParameterType::INTEGER);
            $db->setQuery($query)->execute();

            foreach ($matchIds as $matchId) {
                if (!$db->insertObject('#__sportsmanagement_treeto_match', (object) [
                    'node_id' => $nodeId,
                    'match_id' => $matchId,
                ])) {
                    throw new \RuntimeException('Could not store tournament-tree match.');
                }
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                $db->transactionRollback();
            }

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
        $homeChildTeams = $db->createQuery()
            ->select($db->quoteName('ttn.team_id'))
            ->from($db->quoteName('#__sportsmanagement_treeto_node', 'ttn'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_treeto_node', 'ttn2')
                . ' ON (' . $db->quoteName('ttn.node') . ' = 2 * ' . $db->quoteName('ttn2.node')
                . ' OR ' . $db->quoteName('ttn.node') . ' = 2 * ' . $db->quoteName('ttn2.node') . ' + 1)'
            )
            ->where($db->quoteName('ttn2.id') . ' = :homeChildNodeId')
            ->where($db->quoteName('ttn.treeto_id') . ' = :homeChildTreeId');
        $awayChildTeams = $db->createQuery()
            ->select($db->quoteName('ttn.team_id'))
            ->from($db->quoteName('#__sportsmanagement_treeto_node', 'ttn'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_treeto_node', 'ttn2')
                . ' ON (' . $db->quoteName('ttn.node') . ' = 2 * ' . $db->quoteName('ttn2.node')
                . ' OR ' . $db->quoteName('ttn.node') . ' = 2 * ' . $db->quoteName('ttn2.node') . ' + 1)'
            )
            ->where($db->quoteName('ttn2.id') . ' = :awayChildNodeId')
            ->where($db->quoteName('ttn.treeto_id') . ' = :awayChildTreeId');

        $query = $this->buildMatchQuery()
            ->where($db->quoteName('r.project_id') . ' = :availableProjectId')
            ->where($db->quoteName('mc.projectteam1_id') . ' NOT IN (' . $homeChildTeams . ')')
            ->where($db->quoteName('mc.projectteam2_id') . ' NOT IN (' . $awayChildTeams . ')')
            ->bind(':availableProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':homeChildNodeId', $nodeId, ParameterType::INTEGER)
            ->bind(':homeChildTreeId', $treeId, ParameterType::INTEGER)
            ->bind(':awayChildNodeId', $nodeId, ParameterType::INTEGER)
            ->bind(':awayChildTreeId', $treeId, ParameterType::INTEGER)
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
            ->where($db->quoteName('ttm.node_id') . ' = :nodeMatchesNodeId')
            ->bind(':nodeMatchesNodeId', $nodeId, ParameterType::INTEGER)
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
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('project_type'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getNode(int $nodeId): ?object
    {
        if ($nodeId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto_node'))
            ->where($db->quoteName('id') . ' = :nodeId')
            ->bind(':nodeId', $nodeId, ParameterType::INTEGER);
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
            ->where($db->quoteName('ttm.node_id') . ' = :listNodeId')
            ->bind(':listNodeId', $nodeId, ParameterType::INTEGER)
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

        return $db->createQuery()
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

        return $db->createQuery()
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

    private function strictPositiveId(mixed $raw): int
    {
        if (!is_string($raw) && !is_int($raw)) {
            return 0;
        }

        $raw = trim((string) $raw);

        if (!preg_match('/^[1-9][0-9]*$/D', $raw)) {
            return 0;
        }

        $id = filter_var($raw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    private function normaliseIds(array $ids): array
    {
        $valid = [];

        foreach ($ids as $raw) {
            $id = $this->strictPositiveId($raw);

            if ($id <= 0) {
                throw new \UnexpectedValueException('Invalid tournament-tree match ID.');
            }

            $valid[$id] = $id;
        }

        return array_values($valid);
    }
}

