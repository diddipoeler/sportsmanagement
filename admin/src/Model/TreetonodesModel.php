<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

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
        $nodes = is_array($node) ? $node : [];

        if (!$nodes) {
            return true;
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
                $db->updateObject('#__sportsmanagement_treeto_node', $record, 'id');

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
                $db->insertObject('#__sportsmanagement_treeto_match', $mapping);
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
        $roundQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('tournement') . ' = 1')
            ->order($db->quoteName('roundcode') . ' ASC');
        $db->setQuery($roundQuery);
        $rounds = $db->loadObjectList() ?: [];

        $matchQuery = $db->getQuery(true)
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
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->order($db->quoteName('r.roundcode') . ' DESC')
            ->order($db->quoteName('m.id') . ' ASC');
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
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('roundcode') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /** Delete all nodes/match mappings of a tree and reset its generation state. */
    public function setRemoveNode($post): bool
    {
        $treeId = (int) ((array) $post)['treeto_id'];

        if ($treeId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_treeto_node'))
                ->where($db->quoteName('treeto_id') . ' = ' . $treeId);
            $db->setQuery($query);
            $nodeIds = $this->normaliseIds($db->loadColumn() ?: []);

            if ($nodeIds) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__sportsmanagement_treeto_match'))
                    ->where($db->quoteName('node_id') . ' IN (' . implode(',', $nodeIds) . ')');
                $db->setQuery($query)->execute();
            }

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_treeto_node'))
                ->where($db->quoteName('treeto_id') . ' = ' . $treeId);
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
            $db->updateObject('#__sportsmanagement_treeto', $record, 'id');
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
        $ids = $this->normaliseIds((array) $cid);
        $data = (array) $post;
        $treeId = (int) ($data['treeto_id'] ?? 0);
        $depth = (int) ($data['tree_i'] ?? 0);

        if ($treeId <= 0 || $depth <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            foreach ($ids as $nodeId) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sportsmanagement_treeto_node'))
                    ->set($db->quoteName('is_leaf') . ' = 1')
                    ->where($db->quoteName('id') . ' = ' . $nodeId);
                $db->setQuery($query)->execute();

                $query = $db->getQuery(true)
                    ->select($db->quoteName('node'))
                    ->from($db->quoteName('#__sportsmanagement_treeto_node'))
                    ->where($db->quoteName('id') . ' = ' . $nodeId);
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
                        $query = $db->getQuery(true)
                            ->update($db->quoteName('#__sportsmanagement_treeto_node'))
                            ->set($db->quoteName('published') . ' = 0')
                            ->where($db->quoteName('node') . ' = ' . $child)
                            ->where($db->quoteName('treeto_id') . ' = ' . $treeId);
                        $db->setQuery($query)->execute();
                    }
                }
            }

            $firstBottomNode = (int) pow(2, $depth);
            $lastBottomNode = (int) pow(2, $depth + 1) - 1;
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sportsmanagement_treeto_node'))
                ->set($db->quoteName('is_leaf') . ' = 1')
                ->where($db->quoteName('treeto_id') . ' = ' . $treeId)
                ->where($db->quoteName('node') . ' BETWEEN ' . $firstBottomNode . ' AND ' . $lastBottomNode);
            $db->setQuery($query)->execute();

            $tree = (object) ['id' => $treeId, 'leafed' => 3];
            $db->updateObject('#__sportsmanagement_treeto', $tree, 'id');
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
        $treeId = (int) (((array) $post)['treeto_id'] ?? 0);

        if ($treeId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $tree = (object) ['id' => $treeId, 'leafed' => 1];

        try {
            $db->updateObject('#__sportsmanagement_treeto', $tree, 'id');

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
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name'),
                $db->quoteName('t.middle_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('t.name') . ' ASC');
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

    /** Update selected node team assignments. */
    public function storeshort($cid, $post): bool
    {
        $ids = $this->normaliseIds((array) $cid);
        $data = (array) $post;

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            foreach ($ids as $nodeId) {
                $record = (object) [
                    'id' => $nodeId,
                    'team_id' => (int) ($data['team_id' . $nodeId] ?? 0),
                ];
                $db->updateObject('#__sportsmanagement_treeto_node', $record, 'id');
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
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('id') . ' = ' . $treeId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    protected function getListQuery()
    {
        $this->getState();
        $treeId = (int) $this->getState('context.tree_id', $this->treeId);
        $db = $this->getDatabase();
        $matchCount = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_treeto_match', 'ttm_count'))
            ->where($db->quoteName('ttm_count.node_id') . ' = ' . $db->quoteName('a.id'));

        return $db->getQuery(true)
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
            ->where($db->quoteName('a.treeto_id') . ' = ' . $treeId)
            ->order($db->quoteName('a.row') . ' ASC')
            ->order($db->quoteName('a.node') . ' ASC');
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
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_treeto_match'))
            ->where($db->quoteName('node_id') . ' = ' . $nodeId)
            ->where($db->quoteName('match_id') . ' = ' . $matchId);
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
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id'),
                $db->quoteName('t.name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.id') . ' IN (' . implode(',', $ids) . ')');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $names = [];

        foreach ($rows as $row) {
            $names[(int) $row->id] = (string) $row->name;
        }

        return $names;
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
