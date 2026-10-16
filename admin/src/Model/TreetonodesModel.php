<?php
/**
 * Native Joomla 5/6 list model for tournament-tree nodes.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 list model for tournament-tree nodes. */
final class TreetonodesModel extends SportsManagementListModel
{
    private int $projectId = 0;
    private int $treeId = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'a.row', 'row',
            'a.node', 'node',
            'a.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'a.row', $direction = 'ASC')
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $layout = $input->getCmd('layout');

        if ($layout !== '') {
            $this->context .= '.' . $layout;
        }

        parent::populateState($ordering, $direction);

        $this->projectId = $input->getInt('pid') ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $this->treeId = $input->getInt('tid') ?: (int) $app->getUserState('com_sportsmanagement.tid', 0);

        if ($this->projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->projectId);
        }

        if ($this->treeId > 0) {
            $app->setUserState('com_sportsmanagement.tid', $this->treeId);
        }

        $this->setState('context.project_id', $this->projectId);
        $this->setState('context.tree_id', $this->treeId);

        // A depth-seven bracket contains 255 nodes; this view must never be paged.
        $this->setState('list.limit', 0);
        $this->setState('list.start', 0);
    }

    /** Persist derived node labels/team/round data and add missing positive match links once. */
    public function savenode($node = null): bool
    {
        // Derived bracket display data must never be written from a GET view.
        if (!Session::checkToken('post')) {
            throw new \RuntimeException('Invalid tournament-node write token.', 403);
        }

        $this->assertTreeEditAccess();
        $nodes = is_array($node) ? $node : [];

        if (count($nodes) > 255) {
            $this->setError('Too many tournament nodes to synchronize.');
            return false;
        }

        if (!$nodes) {
            return true;
        }

        $input = $this->administratorApplication()->getInput();
        $treeId = $this->strictNodeId($input->post->get('treeto_id', null, 'raw'));
        $projectId = $this->strictNodeId($input->post->get('project_id', null, 'raw'));
        $ids = [];

        if ($treeId <= 0 || $projectId <= 0) {
            $this->setError('Invalid tournament tree or project for synchronization.');
            return false;
        }

        foreach ($nodes as $value) {
            $id = is_object($value) ? $this->strictNodeId($value->id ?? null) : 0;

            if ($id <= 0 || isset($ids[$id])) {
                $this->setError('Invalid tournament node to synchronize.');
                return false;
            }

            $ids[$id] = $id;
        }

        try {
            if ($this->scopedTree($treeId, $projectId) === null
                || !$this->nodesBelongToTree(array_values($ids), $treeId)) {
                $this->setError('Tournament node synchronization is outside the project.');
                return false;
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $db = $this->getDatabase();
        $modified = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;
        $db->transactionStart();

        try {
            foreach ($nodes as $value) {
                $nodeId = (int) ($value->id ?? 0);

                if ($nodeId <= 0) {
                    continue;
                }

                $record = (object) [
                    'id' => $nodeId,
                    'title' => (string) ($value->title ?? ''),
                    'content' => (string) ($value->content ?? ''),
                    'team_id' => (int) ($value->team_id ?? 0),
                    'roundcode' => (int) ($value->roundcode ?? 0),
                    'modified' => $modified,
                    'modified_by' => $userId,
                ];
                if (!$db->updateObject('#__sportsmanagement_treeto_node', $record, 'id')) {
                    throw new \RuntimeException('Could not synchronize tournament node.');
                }

                $matchId = (int) ($value->match_id ?? 0);

                if ($matchId <= 0 || $this->hasNodeMatch($nodeId, $matchId)) {
                    continue;
                }

                $mapping = (object) [
                    'node_id' => $nodeId,
                    'match_id' => $matchId,
                    'modified' => $modified,
                    'modified_by' => $userId,
                ];
                if (!$db->insertObject('#__sportsmanagement_treeto_match', $mapping)) {
                    throw new \RuntimeException('Could not synchronize tournament match.');
                }
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    /** Rebuild the visual bracket from tournament rounds and their winner chain. */
    public function getteamsprorunde($project_id = 0, $treetows = null): array
    {
        $projectId = (int) $project_id;
        $depth = (int) ($treetows->tree_i ?? 0);

        if ($projectId <= 0 || $depth <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $roundQuery = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :bracketRoundProjectId')
            ->where($db->quoteName('tournement') . ' = 1')
            ->order($db->quoteName('roundcode') . ' ASC')
            ->bind(':bracketRoundProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($roundQuery);
        $rounds = $db->loadObjectList() ?: [];

        $matchQuery = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.next_match_id'),
                $db->quoteName('m.team_won'),
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->where($db->quoteName('r.project_id') . ' = :bracketMatchProjectId')
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->order($db->quoteName('r.roundcode') . ' DESC')
            ->order($db->quoteName('m.id') . ' ASC')
            ->bind(':bracketMatchProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($matchQuery);
        $results = $db->loadObjectList('id') ?: [];

        if (!$rounds || !$results) {
            return $this->emptyBracket($depth);
        }

        $roundCodesWithMatches = array_map(
            static fn ($match): int => (int) $match->roundcode,
            $results
        );
        $maxRoundCode = max($roundCodesWithMatches);
        $projectRoundCode = [];
        $level = 1;

        foreach ($rounds as $round) {
            $roundCode = (int) $round->roundcode;

            if ($roundCode <= $maxRoundCode) {
                $projectRoundCode[$roundCode] = $level++;
            }
        }

        if (!$projectRoundCode || !isset($projectRoundCode[$maxRoundCode])) {
            return $this->emptyBracket($depth);
        }

        arsort($projectRoundCode);
        $nodesPerRound = [];

        for ($roundLevel = 1; $roundLevel <= $depth + 1; $roundLevel++) {
            $nodesPerRound[$roundLevel] = (int) pow(2, max(0, $depth - $roundLevel + 1));
        }

        $nodeRound = [];

        foreach ($projectRoundCode as $roundCode => $roundLevel) {
            $start = $nodesPerRound[$roundLevel] ?? 1;
            $end = $nodesPerRound[$roundLevel - 1] ?? ($start * 2);

            for ($nodeNumber = $start; $nodeNumber < $end; $nodeNumber++) {
                $nodeRound[$nodeNumber] = (int) $roundCode;
            }
        }

        $teamIds = [];

        foreach ($results as $match) {
            $teamIds[] = (int) $match->projectteam1_id;
            $teamIds[] = (int) $match->projectteam2_id;
        }

        $teamNames = $this->getProjectTeamNames($teamIds);
        $matches = [];
        $startTree = $nodesPerRound[$projectRoundCode[$maxRoundCode]];
        $winnersByRoundAndNode = [];

        foreach ($results as $matchId => $match) {
            if ((int) $match->roundcode !== $maxRoundCode) {
                continue;
            }

            $roundLevel = $projectRoundCode[(int) $match->roundcode];
            $nodeNumber = $nodesPerRound[$roundLevel];
            $homeTeamId = (int) $match->projectteam1_id;
            $awayTeamId = (int) $match->projectteam2_id;
            $matches[$nodeNumber] = $this->bracketTeam($homeTeamId, (int) $matchId, $teamNames, $match);
            $nodesPerRound[$roundLevel]++;
            $nodeNumber = $nodesPerRound[$roundLevel];
            $winnersByRoundAndNode[(int) $match->roundcode][$startTree] = $homeTeamId;
            $startTree++;
            $matches[$nodeNumber] = $this->bracketTeam($awayTeamId, (int) $matchId, $teamNames, $match);
            $nodesPerRound[$roundLevel]++;
            $winnersByRoundAndNode[(int) $match->roundcode][$startTree] = $awayTeamId;
        }

        $nextRoundCode = $maxRoundCode - 1;

        for ($roundCode = $maxRoundCode; $roundCode > 0; $roundCode--) {
            foreach (($winnersByRoundAndNode[$roundCode] ?? []) as $nodeNumber => $teamId) {
                foreach ($results as $matchId => $match) {
                    if ((int) $match->roundcode !== $nextRoundCode || (int) $match->team_won !== (int) $teamId) {
                        continue;
                    }

                    $childNode = (int) $nodeNumber * 2;
                    $homeTeamId = (int) $match->projectteam1_id;
                    $awayTeamId = (int) $match->projectteam2_id;
                    $winnersByRoundAndNode[$nextRoundCode][$childNode] = $homeTeamId;
                    $matches[$childNode] = $this->bracketTeam($homeTeamId, (int) $matchId, $teamNames, $match);
                    $childNode++;
                    $winnersByRoundAndNode[$nextRoundCode][$childNode] = $awayTeamId;
                    $matches[$childNode] = $this->bracketTeam($awayTeamId, (int) $matchId, $teamNames, $match);
                    break;
                }
            }

            $nextRoundCode--;
        }

        $lastNodeExclusive = ((int) pow(2, $depth)) * 2;

        for ($nodeNumber = 1; $nodeNumber < $lastNodeExclusive; $nodeNumber++) {
            if (isset($matches[$nodeNumber])) {
                continue;
            }

            $placeholder = new \stdClass();
            $placeholder->team_id = 0;
            $placeholder->match_id = $nodeNumber % 2 !== 0
                ? ($nodeNumber - 1) * -1
                : $nodeNumber * -1;
            $placeholder->team_name = '';
            $placeholder->roundcode = (int) ($nodeRound[$nodeNumber] ?? 0);
            $placeholder->next_match_id = 0;
            $placeholder->team_won = 0;
            $matches[$nodeNumber] = $placeholder;
        }

        ksort($matches);

        return $matches;
    }

    /** Legacy COUNT(roundcode) semantics retained. */
    public function getMaxRound($project_id): int
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('roundcode') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :maxRoundProjectId')
            ->bind(':maxRoundProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /** Delete all nodes/match mappings of a tree and reset its generation state. */
    public function setRemoveNode($post): bool
    {
        $this->assertTreeEditAccess();
        $data = is_array($post) ? $post : [];
        $treeId = $this->strictNodeId($data['treeto_id'] ?? null);
        $projectId = $this->strictNodeId($data['project_id'] ?? null);

        if ($treeId <= 0 || $projectId <= 0) {
            $this->setError('Invalid tournament tree or project.');
            return false;
        }

        $db = $this->getDatabase();

        try {
            if ($this->scopedTree($treeId, $projectId) === null) {
                $this->setError('Tournament tree does not belong to this project.');
                return false;
            }

            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_treeto_node'))
                ->where($db->quoteName('treeto_id') . ' = :removeTreeId')
                ->bind(':removeTreeId', $treeId, ParameterType::INTEGER);
            // A bound lookup prevents a legacy/unbounded tree from creating a
            // massive IN clause when deleting its match mappings.
            $db->setQuery($query, 0, 1001);
            $nodeIds = $this->normaliseIds($db->loadColumn() ?: []);

            if (count($nodeIds) > 1000) {
                $this->setError('Tournament tree contains too many nodes.');
                return false;
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $db->transactionStart();

        try {
            if ($nodeIds) {
                $query = $db->createQuery()
                    ->delete($db->quoteName('#__sportsmanagement_treeto_match'))
                    ->whereIn($db->quoteName('node_id'), $nodeIds, ParameterType::INTEGER);
                $db->setQuery($query)->execute();
            }

            $query = $db->createQuery()
                ->delete($db->quoteName('#__sportsmanagement_treeto_node'))
                ->where($db->quoteName('treeto_id') . ' = :deleteTreeId')
                ->bind(':deleteTreeId', $treeId, ParameterType::INTEGER);
            $db->setQuery($query)->execute();

            $record = (object) [
                'id' => $treeId,
                'tree_i' => 0,
                'global_bestof' => 1,
                'global_matchday' => 0,
                'global_known' => 0,
                'global_fake' => 0,
                'mirror' => 0,
                'hide' => 0,
                'leafed' => 0,
            ];

            if (!$db->updateObject('#__sportsmanagement_treeto', $record, 'id')) {
                throw new \RuntimeException('Could not reset tournament tree.');
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    /** Mark selected nodes as leaves, hide their descendants and prepare final confirmation. */
    public function storeshortleaf($cid, $post): bool
    {
        $this->assertTreeEditAccess();
        $data = is_array($post) ? $post : [];
        $treeId = $this->strictNodeId($data['treeto_id'] ?? null);
        $projectId = $this->strictNodeId($data['project_id'] ?? null);
        $rawDepth = $data['tree_i'] ?? null;
        $depth = $this->strictNodeId($rawDepth);

        if ($treeId <= 0 || $projectId <= 0 || $depth <= 0 || $depth > 7) {
            $this->setError('Invalid tournament tree, project or depth.');
            return false;
        }

        $ids = $this->validatedSubmittedNodes($cid);

        if ($ids === null) {
            return false;
        }

        $db = $this->getDatabase();

        try {
            $tree = $this->scopedTree($treeId, $projectId);

            if ($tree === null || (int) ($tree->tree_i ?? 0) !== $depth
                || !$this->nodesBelongToTree($ids, $treeId)) {
                $this->setError('Selected leaves do not belong to this tournament tree.');
                return false;
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $db->transactionStart();

        try {
            foreach ($ids as $nodeId) {
                $query = $db->createQuery()
                    ->update($db->quoteName('#__sportsmanagement_treeto_node'))
                    ->set($db->quoteName('is_leaf') . ' = 1')
                    ->where($db->quoteName('id') . ' = :leafNodeId')
                    ->where($db->quoteName('treeto_id') . ' = :leafTreeId')
                    ->bind(':leafNodeId', $nodeId, ParameterType::INTEGER)
                    ->bind(':leafTreeId', $treeId, ParameterType::INTEGER);
                $db->setQuery($query)->execute();

                $query = $db->createQuery()
                    ->select($db->quoteName('node'))
                    ->from($db->quoteName('#__sportsmanagement_treeto_node'))
                    ->where($db->quoteName('id') . ' = :leafLookupNodeId')
                    ->where($db->quoteName('treeto_id') . ' = :leafLookupTreeId')
                    ->bind(':leafLookupNodeId', $nodeId, ParameterType::INTEGER)
                    ->bind(':leafLookupTreeId', $treeId, ParameterType::INTEGER);
                $db->setQuery($query, 0, 1);
                $leafNodeNumber = (int) $db->loadResult();

                if ($leafNodeNumber <= 0 || $leafNodeNumber >= pow(2, $depth)) {
                    continue;
                }

                for ($level = 1; $level <= $depth - 1; $level++) {
                    $left = (int) pow(2, $level) * $leafNodeNumber;
                    $right = ((int) pow(2, $level) * ($leafNodeNumber + 1)) - 1;
                    $lastNode = (int) pow(2, $depth + 1);

                    for ($child = $left; $child <= $right && $child < $lastNode; $child++) {
                        $query = $db->createQuery()
                            ->update($db->quoteName('#__sportsmanagement_treeto_node'))
                            ->set($db->quoteName('published') . ' = 0')
                            ->where($db->quoteName('node') . ' = :childNodeNumber')
                            ->where($db->quoteName('treeto_id') . ' = :childTreeId')
                            ->bind(':childNodeNumber', $child, ParameterType::INTEGER)
                            ->bind(':childTreeId', $treeId, ParameterType::INTEGER);
                        $db->setQuery($query)->execute();
                    }
                }
            }

            $firstBottomNode = (int) pow(2, $depth);
            $lastBottomNode = (int) pow(2, $depth + 1) - 1;
            $query = $db->createQuery()
                ->update($db->quoteName('#__sportsmanagement_treeto_node'))
                ->set($db->quoteName('is_leaf') . ' = 1')
                ->where($db->quoteName('treeto_id') . ' = :bottomTreeId')
                ->where($db->quoteName('node') . ' BETWEEN :firstBottomNode AND :lastBottomNode')
                ->bind(':bottomTreeId', $treeId, ParameterType::INTEGER)
                ->bind(':firstBottomNode', $firstBottomNode, ParameterType::INTEGER)
                ->bind(':lastBottomNode', $lastBottomNode, ParameterType::INTEGER);
            $db->setQuery($query)->execute();

            $tree = (object) ['id' => $treeId, 'leafed' => 3];
            if (!$db->updateObject('#__sportsmanagement_treeto', $tree, 'id')) {
                throw new \RuntimeException('Could not update tournament-tree leaf status.');
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    public function storefinishleaf($post): bool
    {
        $this->assertTreeEditAccess();
        $data = is_array($post) ? $post : [];
        $treeId = $this->strictNodeId($data['treeto_id'] ?? null);
        $projectId = $this->strictNodeId($data['project_id'] ?? null);

        if ($treeId <= 0 || $projectId <= 0) {
            $this->setError('Invalid tournament tree or project.');
            return false;
        }

        $db = $this->getDatabase();

        try {
            if ($this->scopedTree($treeId, $projectId) === null) {
                $this->setError('Tournament tree does not belong to this project.');
                return false;
            }

            if (!$db->updateObject('#__sportsmanagement_treeto', (object) [
                'id' => $treeId,
                'leafed' => 1,
            ], 'id')) {
                throw new \RuntimeException('Could not finalise tournament-tree leaves.');
            }

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getProjectTeamsOptions($project_id = 0): array
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            $this->getState();
            $projectId = (int) $this->getState('context.project_id', $this->projectId);
        }

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name'),
                $db->quoteName('t.middle_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = :teamListProjectId')
            ->order($db->quoteName('t.name') . ' ASC')
            ->bind(':teamListProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $name = (string) $row->name;
            $row->text = mb_strlen($name) < 45 || empty($row->middle_name)
                ? $name
                : (string) $row->middle_name;
            unset($row->name, $row->middle_name);
        }

        return $rows;
    }

    /** Update node teams only when both the nodes and teams belong to the tree project. */
    public function storeshort($cid, $post): bool
    {
        $this->assertTreeEditAccess();
        $data = is_array($post) ? $post : [];
        $treeId = $this->strictNodeId($data['treeto_id'] ?? null);
        $projectId = $this->strictNodeId($data['project_id'] ?? null);
        $ids = $this->validatedSubmittedNodes($cid);

        if ($ids === null) {
            return false;
        }

        if ($treeId <= 0 || $projectId <= 0) {
            $this->setError('Invalid tournament tree or project.');
            return false;
        }

        $teams = [];

        foreach ($ids as $nodeId) {
            $rawTeam = $data['team_id' . $nodeId] ?? 0;
            $teamId = ($rawTeam === 0 || $rawTeam === '0' || $rawTeam === '')
                ? 0 : $this->strictNodeId($rawTeam);

            if ($teamId <= 0 && !in_array($rawTeam, [0, '0', ''], true)) {
                $this->setError('Invalid project-team ID.');
                return false;
            }

            $teams[$nodeId] = $teamId;
        }

        $db = $this->getDatabase();

        try {
            if ($this->scopedTree($treeId, $projectId) === null
                || !$this->nodesBelongToTree($ids, $treeId)) {
                $this->setError('Selected tournament nodes belong to another tree.');
                return false;
            }

            $teamIds = array_values(array_unique(array_filter(array_values($teams))));

            if ($teamIds !== []) {
                $query = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_project_team'))
                    ->whereIn($db->quoteName('id'), $teamIds, ParameterType::INTEGER)
                    ->where($db->quoteName('project_id') . ' = :nodeTeamsProjectId')
                    ->bind(':nodeTeamsProjectId', $projectId, ParameterType::INTEGER);
                $db->setQuery($query, 0, 500);
                $found = array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));
                sort($found);
                sort($teamIds);

                if ($found !== $teamIds) {
                    $this->setError('Selected teams do not belong to the tournament project.');
                    return false;
                }
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        if ($ids === []) {
            return true;
        }

        $db->transactionStart();

        try {
            foreach ($ids as $nodeId) {
                if (!$db->updateObject('#__sportsmanagement_treeto_node', (object) [
                    'id' => $nodeId,
                    'team_id' => $teams[$nodeId],
                ], 'id')) {
                    throw new \RuntimeException('Could not store tournament node team.');
                }
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getProject(int $projectId = 0): ?object
    {
        if ($projectId <= 0) {
            $this->getState();
            $projectId = (int) $this->getState('context.project_id', $this->projectId);
        }

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
            ->where($db->quoteName('id') . ' = :treeProjectId')
            ->bind(':treeProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getTreeToData(int $treeId = 0): ?object
    {
        if ($treeId <= 0) {
            $this->getState();
            $treeId = (int) $this->getState('context.tree_id', $this->treeId);
        }

        if ($treeId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('id') . ' = :treeDataId')
            ->bind(':treeDataId', $treeId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    protected function getListQuery()
    {
        $this->getState();
        $treeId = (int) $this->getState('context.tree_id', $this->treeId);
        $db = $this->getDatabase();
        $matchCount = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_treeto_match', 'ttm_count'))
            ->where($db->quoteName('ttm_count.node_id') . ' = ' . $db->quoteName('a.id'));

        $query = $db->createQuery()
            ->select([
                $db->quoteName('a') . '.*',
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('tt.tree_i'),
                '(' . $matchCount . ') AS ' . $db->quoteName('countmatch'),
            ])
            ->from($db->quoteName('#__sportsmanagement_treeto_node', 'a'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('a.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_treeto', 'tt') . ' ON ' . $db->quoteName('tt.id') . ' = ' . $db->quoteName('a.treeto_id'))
            ->where($db->quoteName('a.treeto_id') . ' = :listTreeId')
            ->order($db->quoteName('a.row') . ' ASC')
            ->order($db->quoteName('a.node') . ' ASC')
            ->bind(':listTreeId', $treeId, ParameterType::INTEGER);

        return $query;
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (int) $this->getState('context.project_id');
        $id .= ':' . (int) $this->getState('context.tree_id');

        return parent::getStoreId($id);
    }

    private function hasNodeMatch(int $nodeId, int $matchId): bool
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_treeto_match'))
            ->where($db->quoteName('node_id') . ' = :nodeMatchNodeId')
            ->where($db->quoteName('match_id') . ' = :nodeMatchMatchId')
            ->bind(':nodeMatchNodeId', $nodeId, ParameterType::INTEGER)
            ->bind(':nodeMatchMatchId', $matchId, ParameterType::INTEGER);
        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    private function bracketTeam(int $teamId, int $matchId, array $teamNames, object $match): object
    {
        $object = new \stdClass();
        $object->team_id = $teamId;
        $object->match_id = $matchId;
        $object->team_name = $teamNames[$teamId] ?? '';
        $object->roundcode = (int) $match->roundcode;
        $object->next_match_id = (int) $match->next_match_id;
        $object->team_won = (int) $match->team_won;

        return $object;
    }

    private function emptyBracket(int $depth): array
    {
        $matches = [];
        $lastNodeExclusive = ((int) pow(2, $depth)) * 2;

        for ($nodeNumber = 1; $nodeNumber < $lastNodeExclusive; $nodeNumber++) {
            $placeholder = new \stdClass();
            $placeholder->team_id = 0;
            $placeholder->match_id = $nodeNumber % 2 !== 0
                ? ($nodeNumber - 1) * -1
                : $nodeNumber * -1;
            $placeholder->team_name = '';
            $placeholder->roundcode = 0;
            $placeholder->next_match_id = 0;
            $placeholder->team_won = 0;
            $matches[$nodeNumber] = $placeholder;
        }

        return $matches;
    }

    private function getProjectTeamNames(array $teamIds): array
    {
        $ids = $this->normaliseIds($teamIds);

        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id'),
                $db->quoteName('t.name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->whereIn($db->quoteName('pt.id'), $ids, ParameterType::INTEGER);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $names = [];

        foreach ($rows as $row) {
            $names[(int) $row->id] = (string) $row->name;
        }

        return $names;
    }

    private function assertTreeEditAccess(): void
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorized to edit tournament-tree nodes.', 403);
        }
    }

    private function strictNodeId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $value = trim((string) $value);

        if (!preg_match('/^[1-9][0-9]*$/D', $value)) {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    private function validatedSubmittedNodes(mixed $selected): ?array
    {
        if (!is_array($selected) || count($selected) > 500) {
            $this->setError('Invalid tournament-node selection.');
            return null;
        }

        $ids = [];

        foreach ($selected as $rawId) {
            $id = $this->strictNodeId($rawId);

            if ($id <= 0) {
                $this->setError('Invalid tournament-node ID.');
                return null;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    private function scopedTree(int $treeId, int $projectId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('tree_i')])
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('id') . ' = :nodeTreeId')
            ->where($db->quoteName('project_id') . ' = :nodeTreeProjectId')
            ->bind(':nodeTreeId', $treeId, ParameterType::INTEGER)
            ->bind(':nodeTreeProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function nodesBelongToTree(array $ids, int $treeId): bool
    {
        if ($ids === []) {
            return true;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_treeto_node'))
            ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER)
            ->where($db->quoteName('treeto_id') . ' = :selectedTreeId')
            ->bind(':selectedTreeId', $treeId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 500);
        $found = array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));
        $expected = $ids;
        sort($found);
        sort($expected);

        return $found === $expected;
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
