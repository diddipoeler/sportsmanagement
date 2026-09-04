<?php
/**
 * Native Joomla 5/6 administrator form model for one project team.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectteamTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/** Native Joomla 5/6 administrator form model for one project team. */
final class ProjectteamModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.projectteam',
            'projectteam',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'Projectteam', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Projectteam') === 0) {
            return new ProjectteamTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function set_playground_match($post = null): bool
    {
        $input = $this->administratorApplication()->getInput();
        $data = is_array($post) ? $post : $input->post->getArray();
        $ids = $this->normaliseIds($data['cid'] ?? $input->post->get('cid', [], 'array'));
        $db = $this->getDatabase();

        foreach ($ids as $projectTeamId) {
            $seasonTeamId = $this->projectTeamSeasonId($projectTeamId);
            $playgroundId = $this->getProjectTeamPlayground($seasonTeamId);

            if ($playgroundId <= 0) {
                continue;
            }

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__sportsmanagement_match'))
                ->set($db->quoteName('playground_id') . ' = ' . $playgroundId)
                ->where($db->quoteName('projectteam1_id') . ' = ' . $projectTeamId);

            if (!$this->execute($query)) {
                return false;
            }
        }

        return true;
    }

    public function getProjectTeamPlayground($team_id = 0): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('c.standard_playground'))
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('st.id') . ' = ' . (int) $team_id);

        try {
            $db->setQuery($query, 0, 1);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return 0;
        }
    }

    public function set_playground($post = null): bool
    {
        $input = $this->administratorApplication()->getInput();
        $data = is_array($post) ? $post : $input->post->getArray();
        $ids = $this->normaliseIds($data['cid'] ?? $input->post->get('cid', [], 'array'));
        $db = $this->getDatabase();

        foreach ($ids as $projectTeamId) {
            $playgroundId = $this->getProjectTeamPlayground($this->projectTeamSeasonId($projectTeamId));

            if ($playgroundId <= 0) {
                continue;
            }

            $record = (object) ['id' => $projectTeamId, 'standard_playground' => $playgroundId];

            try {
                $db->updateObject('#__sportsmanagement_project_team', $record, 'id');
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        return true;
    }

    public function saveshort(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $ids = $this->normaliseIds($post['cid'] ?? []);
        $db = $this->getDatabase();
        $projectId = (int) ($post['pid'] ?? 0);
        $targetProjectId = (int) ($post['all_project_id'] ?? $projectId);
        $date = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $associationId = $this->projectAssociation($projectId);
        $divisionPoints = is_array($post['division_points'] ?? null) ? $post['division_points'] : [];
        $result = true;

        foreach ($ids as $id) {
            $record = (object) [
                'id' => $id,
                'division_id' => (int) ($post['division_id' . $id] ?? 0),
                'start_points' => (string) ($post['start_points' . $id] ?? '0'),
                'penalty_points' => (string) ($post['penalty_points' . $id] ?? '0'),
                'is_in_score' => (int) ($post['is_in_score' . $id] ?? 0),
                'use_finally' => (int) ($post['use_finally' . $id] ?? 0),
                'finaltablerank' => (int) ($post['finaltablerank' . $id] ?? 0),
                'champion' => (int) ($post['champion' . $id] ?? 0),
                'points_finally' => (string) ($post['points_finally' . $id] ?? '0'),
                'neg_points_finally' => (string) ($post['neg_points_finally' . $id] ?? '0'),
                'matches_finally' => (int) ($post['matches_finally' . $id] ?? 0),
                'won_finally' => (int) ($post['won_finally' . $id] ?? 0),
                'draws_finally' => (int) ($post['draws_finally' . $id] ?? 0),
                'lost_finally' => (int) ($post['lost_finally' . $id] ?? 0),
                'homegoals_finally' => (int) ($post['homegoals_finally' . $id] ?? 0),
                'guestgoals_finally' => (int) ($post['guestgoals_finally' . $id] ?? 0),
                'diffgoals_finally' => (int) ($post['diffgoals_finally' . $id] ?? 0),
                'modified' => $date,
                'modified_by' => $userId,
            ];

            $record->project_id = $projectId !== $targetProjectId
                ? $targetProjectId
                : (int) ($post['new_project_id' . $id] ?? $projectId);

            try {
                $db->updateObject('#__sportsmanagement_project_team', $record, 'id');
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());
                $result = false;
            }

            $clubId = (int) ($post['club_id' . $id] ?? 0);

            if ($clubId > 0) {
                $club = (object) [
                    'id' => $clubId,
                    'location' => trim((string) ($post['location' . $id] ?? '')),
                    'name' => trim((string) ($post['clubname' . $id] ?? '')),
                    'zipcode' => trim((string) ($post['zipcode' . $id] ?? '')),
                    'address' => trim((string) ($post['address' . $id] ?? '')),
                    'founded_year' => trim((string) ($post['founded_year' . $id] ?? '')),
                    'unique_id' => trim((string) ($post['unique_id' . $id] ?? '')),
                    'modified' => $date,
                    'modified_by' => $userId,
                ];

                if ($associationId > 0 && !$this->clubHasAssociation($clubId)) {
                    $club->associations = $associationId;
                }

                try {
                    $db->updateObject('#__sportsmanagement_club', $club, 'id');
                } catch (\Throwable $e) {
                    $this->setError($e->getMessage());
                    $result = false;
                }
            }

            $teamId = (int) ($post['team_id' . $id] ?? 0);

            if ($teamId > 0) {
                try {
                    $db->updateObject(
                        '#__sportsmanagement_team',
                        (object) [
                            'id' => $teamId,
                            'name' => trim((string) ($post['teamname' . $id] ?? '')),
                            'modified' => $date,
                            'modified_by' => $userId,
                        ],
                        'id'
                    );
                } catch (\Throwable $e) {
                    $this->setError($e->getMessage());
                    $result = false;
                }
            }

            foreach ((array) ($divisionPoints[$id] ?? []) as $divisionId => $values) {
                if (!$this->updateDivisionPoints($id, (int) $divisionId, (array) $values)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    public function setseasonid(): bool
    {
        $input = $this->administratorApplication()->getInput();
        $ids = $this->normaliseIds($input->post->get('cid', [], 'array'));
        $seasonId = $input->post->getInt('season_id');
        $db = $this->getDatabase();

        foreach ($ids as $projectTeamId) {
            $seasonTeamId = $this->projectTeamSeasonId($projectTeamId);

            if ($seasonTeamId <= 0) {
                continue;
            }

            try {
                $db->updateObject(
                    '#__sportsmanagement_season_team_id',
                    (object) ['id' => $seasonTeamId, 'season_id' => $seasonId],
                    'id'
                );
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        return true;
    }

    public function setusetable($setzer = 0): bool
    {
        return $this->updateSelectedProjectTeams('is_in_score', (int) $setzer);
    }

    public function setusetablepoints($setzer = 0): bool
    {
        return $this->updateSelectedProjectTeams('use_finally', (int) $setzer);
    }

    public function matchgroups(): bool
    {
        $input = $this->administratorApplication()->getInput();
        $post = $input->post->getArray();
        $ids = $this->normaliseIds($post['cid'] ?? []);
        $db = $this->getDatabase();

        foreach ($ids as $projectTeamId) {
            $divisionId = (int) ($post['division_id' . $projectTeamId] ?? 0);

            foreach (['projectteam1_id', 'projectteam2_id'] as $field) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sportsmanagement_match'))
                    ->set($db->quoteName('division_id') . ' = ' . $divisionId)
                    ->where($db->quoteName($field) . ' = ' . $projectTeamId);

                if (!$this->execute($query)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function storeAssign($post = null): bool
    {
        $input = $this->administratorApplication()->getInput();
        $data = is_array($post) ? $post : $input->post->getArray();
        $projectId = (int) ($data['project_id'] ?? 0);
        $assignedSeasonTeamIds = $this->normaliseIds($data['project_teamslist'] ?? []);
        $deleteTeamIds = $this->normaliseIds($data['teamslist'] ?? []);
        $db = $this->getDatabase();
        $deleteProjectTeamIds = [];

        if ($deleteTeamIds) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('pt.id'))
                ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
                ->where($db->quoteName('st.team_id') . ' IN (' . implode(',', $deleteTeamIds) . ')');

            try {
                $db->setQuery($query);
                $deleteProjectTeamIds = array_map('intval', $db->loadColumn() ?: []);
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        foreach ($assignedSeasonTeamIds as $seasonTeamId) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_project_team'))
                ->where($db->quoteName('team_id') . ' = ' . $seasonTeamId)
                ->where($db->quoteName('project_id') . ' = ' . $projectId);

            try {
                $db->setQuery($query);

                if ((int) $db->loadResult() === 0) {
                    $db->insertObject(
                        '#__sportsmanagement_project_team',
                        (object) ['project_id' => $projectId, 'team_id' => $seasonTeamId]
                    );
                }
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        if ($deleteProjectTeamIds) {
            return $this->delete($deleteProjectTeamIds);
        }

        return true;
    }

    public function delete(&$pks)
    {
        $ids = $this->normaliseIds($pks);

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $idList = implode(',', $ids);

        foreach (['projectteam1_id', 'projectteam2_id'] as $field) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__sportsmanagement_match'))
                ->where($db->quoteName($field) . ' IN (' . $idList . ')');

            if (!$this->execute($query)) {
                return false;
            }
        }

        return parent::delete($ids);
    }

    public function getProjectTeam($team_id = 0)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->where($db->quoteName('st.team_id') . ' = ' . (int) $team_id);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    private function updateSelectedProjectTeams(string $field, int $value): bool
    {
        if (!in_array($field, ['is_in_score', 'use_finally'], true)) {
            return false;
        }

        $ids = $this->normaliseIds($this->administratorApplication()->getInput()->post->get('cid', [], 'array'));
        $db = $this->getDatabase();

        foreach ($ids as $id) {
            try {
                $db->updateObject('#__sportsmanagement_project_team', (object) ['id' => $id, $field => $value], 'id');
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        return true;
    }

    private function updateDivisionPoints(int $projectTeamId, int $divisionId, array $values): bool
    {
        $allowed = [
            'start_points', 'matches_finally', 'points_finally', 'neg_points_finally',
            'penalty_points', 'won_finally', 'draws_finally', 'lost_finally',
            'homegoals_finally', 'guestgoals_finally', 'diffgoals_finally',
        ];
        $db = $this->getDatabase();
        $sets = [];

        foreach ($values as $field => $value) {
            $field = trim((string) $field, "'\"");

            if (!in_array($field, $allowed, true)) {
                continue;
            }

            $sets[] = $db->quoteName($field) . ' = ' . $db->quote((string) $value);
        }

        if (!$sets || $projectTeamId <= 0 || $divisionId <= 0) {
            return true;
        }

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_project_team_division'))
            ->set($sets)
            ->where($db->quoteName('team_id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('division_id') . ' = ' . $divisionId);

        return $this->execute($query);
    }

    private function projectTeamSeasonId(int $projectTeamId): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('team_id'))
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('id') . ' = ' . $projectTeamId);

        try {
            $db->setQuery($query, 0, 1);

            return (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return 0;
        }
    }

    private function projectAssociation(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('l.associations'))
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);

            return (int) $db->loadResult();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function clubHasAssociation(int $clubId): bool
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('associations'))
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->where($db->quoteName('id') . ' = ' . $clubId);

        try {
            $db->setQuery($query, 0, 1);

            return (int) $db->loadResult() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function execute($query): bool
    {
        $db = $this->getDatabase();

        try {
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    private function normaliseIds($ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
