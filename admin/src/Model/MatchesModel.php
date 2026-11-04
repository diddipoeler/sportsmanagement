<?php
/**
 * Native Joomla 5/6 administrator list model for matches.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 administrator list model for matches. */
final class MatchesModel extends SportsManagementListModel
{
    /**
     * Configure the native administrator match list model and sortable fields.
     * Registers match date, number, ordering and publication columns
     * with Joomla's list-model factory while retaining explicitly
     * provided filter fields in caller configuration.
     * @param array $config Joomla model configuration options.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'mc.match_number', 'match_number',
            'mc.match_date', 'match_date',
            'mc.id', 'id',
            'mc.ordering', 'ordering',
            'mc.published', 'published', 'state',
        ];
        parent::__construct($config, $factory);
    }

    /**
     * Restore match-list project, round, team and season context.
     * Reads administrator request selections or remembered Joomla user
     * state, resets incompatible round/team selectors, and applies list
     * filters for the selected match management view.
     * @param string $ordering Default fixture sort field.
     * @param string $direction Default ordering direction.
     * @return void
     */
    protected function populateState($ordering = 'mc.match_date', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();
        $option = 'com_sportsmanagement';

        $projectId = $input->getInt('pid') ?: (int) $app->getUserState($option . '.pid', 0);
        $roundId = $input->getInt('rid') ?: (int) $app->getUserState($option . '.rid', 0);
        $projectTeamId = $input->getInt('projectteam');
        $seasonId = $input->getInt('season_id') ?: (int) $app->getUserState($option . '.season_id', 0);

        if ($projectTeamId > 0) {
            $roundId = 0;
        }

        $this->setState('context.project_id', $projectId);
        $this->setState('context.round_id', $roundId);
        $this->setState('context.project_team_id', $projectTeamId);
        $this->setState('context.season_id', $seasonId);
        $this->setState(
            'filter.division',
            $this->getUserStateFromRequest($this->context . '.filter.division', 'filter_division', 0, 'int')
        );

        if ($projectId > 0) {
            $app->setUserState($option . '.pid', $projectId);
        }
        if ($roundId > 0) {
            $app->setUserState($option . '.rid', $roundId);
        }
        if ($seasonId > 0) {
            $app->setUserState($option . '.season_id', $seasonId);
        }
    }

    /**
     * Build the filtered backend list query for fixtures.
     * Joins match rows with editorial and division information and
     * restricts results by selected project, round, team and other
     * administrator filters while applying approved sort columns.
     * @return \Joomla\Database\DatabaseQuery Joomla match-list query.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $roundId = (int) $this->getState('context.round_id');
        $projectTeamId = (int) $this->getState('context.project_team_id');
        $divisionId = (int) $this->getState('filter.division');

        $query = $db->createQuery()
            ->select([
                $db->quoteName('mc') . '.*',
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('divhome.id', 'divhomeid'),
                $db->quoteName('divaway.id', 'divawayid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'mc'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('mc.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pthome')
                . ' ON ' . $db->quoteName('pthome.id') . ' = ' . $db->quoteName('mc.projectteam1_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'ptaway')
                . ' ON ' . $db->quoteName('ptaway.id') . ' = ' . $db->quoteName('mc.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_division', 'divhome')
                . ' ON ' . $db->quoteName('divhome.id') . ' = ' . $db->quoteName('pthome.division_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_division', 'divaway')
                . ' ON ' . $db->quoteName('divaway.id') . ' = ' . $db->quoteName('ptaway.division_id')
            );

        if ($roundId > 0) {
            $query->where($db->quoteName('mc.round_id') . ' = :roundId')
                ->bind(':roundId', $roundId, ParameterType::INTEGER);
        }

        if ($projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('mc.projectteam1_id') . ' = :homeProjectTeamId'
                . ' OR ' . $db->quoteName('mc.projectteam2_id') . ' = :awayProjectTeamId)'
            )
                ->bind(':homeProjectTeamId', $projectTeamId, ParameterType::INTEGER)
                ->bind(':awayProjectTeamId', $projectTeamId, ParameterType::INTEGER);
        }

        if ($divisionId > 0) {
            $query->where(
                '(' . $db->quoteName('divhome.id') . ' = :homeDivisionId'
                . ' OR ' . $db->quoteName('divaway.id') . ' = :awayDivisionId)'
            )
                ->bind(':homeDivisionId', $divisionId, ParameterType::INTEGER)
                ->bind(':awayDivisionId', $divisionId, ParameterType::INTEGER);
        }

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            if (ctype_digit($search)) {
                $searchId = (int) $search;
                $query->where(
                    '(' . $db->quoteName('mc.id') . ' = :searchId'
                    . ' OR ' . $db->quoteName('mc.match_number') . ' = :searchMatchNumber)'
                )
                    ->bind(':searchId', $searchId, ParameterType::INTEGER)
                    ->bind(':searchMatchNumber', $searchId, ParameterType::INTEGER);
            } else {
                $needle = '%' . $db->escape($search, true) . '%';
                $query->where(
                    '(' . $db->quoteName('mc.notes') . ' LIKE :searchNotes'
                    . ' OR ' . $db->quoteName('mc.matchcode') . ' LIKE :searchMatchcode)'
                )
                    ->bind(':searchNotes', $needle, ParameterType::STRING)
                    ->bind(':searchMatchcode', $needle, ParameterType::STRING);
            }
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('mc.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        }

        $orderMap = [
            'mc.match_number' => $db->quoteName('mc.match_number'),
            'match_number' => $db->quoteName('mc.match_number'),
            'mc.match_date' => $db->quoteName('mc.match_date'),
            'match_date' => $db->quoteName('mc.match_date'),
            'mc.id' => $db->quoteName('mc.id'),
            'id' => $db->quoteName('mc.id'),
            'mc.ordering' => $db->quoteName('mc.ordering'),
            'ordering' => $db->quoteName('mc.ordering'),
            'mc.published' => $db->quoteName('mc.published'),
            'published' => $db->quoteName('mc.published'),
            'state' => $db->quoteName('mc.published'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'mc.match_date');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['mc.match_date']) . ' ' . $direction);

        return $query;
    }

    /** Add roster/referee counters used by the administrator match list. */
    public function prepareItems($items)
    {
        $seasonId = (int) $this->getState('context.season_id');

        foreach ((array) $items as $item) {
            $matchId = (int) ($item->id ?? 0);
            $homeProjectTeamId = (int) ($item->projectteam1_id ?? 0);
            $awayProjectTeamId = (int) ($item->projectteam2_id ?? 0);

            $item->homeplayers_count = $this->countRosterRows(
                '#__sportsmanagement_match_player',
                'teamplayer_id',
                $matchId,
                $homeProjectTeamId,
                $seasonId,
                1,
                true
            );
            $item->homestaff_count = $this->countRosterRows(
                '#__sportsmanagement_match_staff',
                'team_staff_id',
                $matchId,
                $homeProjectTeamId,
                $seasonId,
                2
            );
            $item->awayplayers_count = $this->countRosterRows(
                '#__sportsmanagement_match_player',
                'teamplayer_id',
                $matchId,
                $awayProjectTeamId,
                $seasonId,
                1,
                true
            );
            $item->awaystaff_count = $this->countRosterRows(
                '#__sportsmanagement_match_staff',
                'team_staff_id',
                $matchId,
                $awayProjectTeamId,
                $seasonId,
                2
            );

            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select([
                    'COUNT(' . $db->quoteName('id') . ') AS ' . $db->quoteName('total'),
                    'MIN(' . $db->quoteName('project_referee_id') . ') AS ' . $db->quoteName('referee_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_referee'))
                ->where($db->quoteName('match_id') . ' = :matchId')
                ->bind(':matchId', $matchId, ParameterType::INTEGER);
            $db->setQuery($query);
            $referee = $db->loadObject();
            $item->referees_count = (int) ($referee->total ?? 0);
            $item->referee_id = (int) ($referee->referee_id ?? 0);
        }

        return $items;
    }

    /**
     * Verify or create the image folder for one valid match report.
     * Validates the numeric fixture ID before building a path beneath
     * the component media directory and only then ensures the folder
     * exists for uploaded match photographs.
     * @param mixed $matchId Match ID or Joomla ID:alias route segment.
     * @return bool Whether the match-report picture directory is available.
     */
    public function checkMatchPicturePath($matchId): bool
    {
        $matchId = self::strictMatchId($matchId);
        if ($matchId <= 0) {
            return false;
        }

        $folder = 'matchreport/' . $matchId;
        $destination = JPATH_ROOT . '/images/com_sportsmanagement/database/' . $folder;
        $this->setState('folder', $folder);

        return is_dir($destination) || @mkdir($destination, 0775, true) || is_dir($destination);
    }

    /**
     * Count fixtures in a project, optionally for a selected team.
     * Uses a bound project ID and an optional home/away team filter
     * to support match totals in administrator project reports.
     * @param mixed $projectId Project identifier for the fixture count.
     * @param mixed $projectTeamId Optional project-team relation ID.
     * @return int Number of matching fixtures.
     */
    public function getMatchesCount($projectId = 0, $projectTeamId = 0)
    {
        $projectId = (int) $projectId;
        $projectTeamId = (int) $projectTeamId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('m.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('m.projectteam1_id') . ' = :countHomeProjectTeamId'
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = :countAwayProjectTeamId)'
            )
                ->bind(':countHomeProjectTeamId', $projectTeamId, ParameterType::INTEGER)
                ->bind(':countAwayProjectTeamId', $projectTeamId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    /**
     * Return fixtures assigned to the requested round.
     * Rejects invalid round IDs and queries the match table with an
     * integer binding, ordering fixtures chronologically.
     * @param mixed $roundId Requested match round.
     * @return array<int,object> Fixture records in date order.
     */
    public function getMatchesByRound($roundId)
    {
        $roundId = (int) $roundId;
        if ($roundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->where($db->quoteName('m.round_id') . ' = :roundId')
            ->order($db->quoteName('m.match_date') . ' ASC')
            ->bind(':roundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Load the project and sport-type data for match management.
     * Uses the bound project ID and a left join to sport metadata,
     * returning null for missing or invalid project selections.
     * @param int $projectId Project to look up.
     * @return object|null Project with sport name or null.
     */
    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p') . '.*',
                $db->quoteName('st.name', 'sport_type_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * Load one match round by its numeric identifier.
     * Rejects nonpositive round IDs and returns a single Joomla
     * database record if that round exists.
     * @param int $roundId Requested round identifier.
     * @return object|null Match round record or null.
     */
    public function getRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('r') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.id') . ' = :roundId')
            ->bind(':roundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * List the rounds associated with a specific project.
     * Fetches round names, dates and ordering with a bound project
     * ID for the administrator match-round selectors.
     * @param int $projectId Project whose rounds are needed.
     * @return array<int,object> Sorted project rounds.
     */
    public function getRounds(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.name'),
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                $db->quoteName('r.ordering'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->order($db->quoteName('r.ordering') . ' ASC')
            ->order($db->quoteName('r.id') . ' ASC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Build match-editor team selector options scoped to a project.
     * Joins project-team and season-team records to team names and
     * optionally filters by division for the selected project.
     * @param int $projectId Project owning the teams.
     * @param int $divisionId Optional division restriction.
     * @return array<int,object> Project team value/text options.
     */
    public function getProjectTeamOptions(int $projectId, int $divisionId = 0): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
                $db->quoteName('pt.division_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->order($db->quoteName('t.name') . ' ASC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        if ($divisionId > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = :divisionId')
                ->bind(':divisionId', $divisionId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * List divisions represented by teams in the current project.
     * Uses a project-scoped join and grouping so each available
     * division appears once in the administrator fixture filters.
     * @param int $projectId Project whose divisions are requested.
     * @return array<int,object> Division value/text options.
     */
    public function getDivisionOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('d.id', 'value'),
                $db->quoteName('d.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.division_id') . ' = ' . $db->quoteName('d.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->group([$db->quoteName('d.id'), $db->quoteName('d.name')])
            ->order($db->quoteName('d.name') . ' ASC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Resolve available venues for a selection of project teams.
     * Collects project-team IDs, joins related playground records and
     * returns display names and pictures suitable for a match editor.
     * @param array<int,object> $projectTeams Selected project-team options.
     * @return array<int,object> Venue selection records.
     */
    public function getPlaygroundOptions(array $projectTeams): array
    {
        $ids = [];
        foreach ($projectTeams as $projectTeam) {
            $id = (int) ($projectTeam->value ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        if (!$ids) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id', 'value'),
                'CONCAT(' . $db->quoteName('p.name') . ', ' . $db->quote(' (') . ', '
                    . $db->quoteName('p.short_name') . ', ' . $db->quote(')') . ') AS ' . $db->quoteName('text'),
                $db->quoteName('p.picture', 'playgroundpicture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'p'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'club')
                . ' ON ' . $db->quoteName('club.standard_playground') . ' = ' . $db->quoteName('p.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 'team')
                . ' ON ' . $db->quoteName('team.club_id') . ' = ' . $db->quoteName('club.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'steam')
                . ' ON ' . $db->quoteName('steam.team_id') . ' = ' . $db->quoteName('team.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('steam.id')
            )
            ->whereIn($db->quoteName('pt.id'), $ids, ParameterType::INTEGER)
            ->group([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.short_name'),
                $db->quoteName('p.picture'),
            ])
            ->order($db->quoteName('p.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Discover the physical match-table columns used by the editor.
     * Asks Joomla's database driver for the match schema instead
     * of relying on a deprecated table introspection helper.
     * @return array<string,mixed> Match table column metadata.
     */
    public function getMatchTableColumns(): array
    {
        return $this->getDatabase()->getTableColumns('#__sportsmanagement_match', false) ?: [];
    }

    /**
     * Expose the active match administration routing parameters.
     * Returns remembered project, round, team and season identifiers
     * so list and edit actions can preserve their selection context.
     * @return array<string,int> Current administrator match context.
     */
    public function getContextParams(): array
    {
        return [
            'pid' => (int) $this->getState('context.project_id'),
            'rid' => (int) $this->getState('context.round_id'),
            'projectteam' => (int) $this->getState('context.project_team_id'),
            'season_id' => (int) $this->getState('context.season_id'),
        ];
    }

    /**
     * Parse a positive fixture ID without partial integer conversion.
     * Accepts decimal IDs and standard Joomla ID:alias slugs, rejecting
     * booleans, arrays, negative values, malformed slugs and overflow.
     * @param mixed $value Raw match identifier.
     * @return int Valid positive match ID or zero.
     */
    private static function strictMatchId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);
        if (!preg_match('/^([0-9]+)(?::[^:\s,;]+)?$/D', $raw, $matches)) {
            return 0;
        }

        $digits = ltrim($matches[1], '0');
        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Count selected roster records associated with a fixture and team.
     * Joins match-player or match-staff rows to season-team relations,
     * filters by match, project team and season, and respects the
     * requested person category and player-only setting.
     * @param string $matchTable Trusted match-player or match-staff table.
     * @param string $relationField Trusted roster relation column.
     * @param int $matchId Fixture ID to count.
     * @param int $projectTeamId Selected project-team relation.
     * @param int $seasonId Current competition season.
     * @param int $personType Roster person category.
     * @param bool $playersOnly Whether to limit to player entries.
     * @return int Number of matching roster records.
     */
    private function countRosterRows(
        string $matchTable,
        string $relationField,
        int $matchId,
        int $projectTeamId,
        int $seasonId,
        int $personType,
        bool $playersOnly = false
    ): int {
        if ($matchId <= 0 || $projectTeamId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('mr.id') . ')')
            ->from($db->quoteName($matchTable, 'mr'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('mr.' . $relationField)
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('mr.match_id') . ' = :matchId')
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->where($db->quoteName('tp.season_id') . ' = :seasonId')
            ->where($db->quoteName('tp.persontype') . ' = :personType')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);

        if ($playersOnly) {
            $query->where(
                '(' . $db->quoteName('mr.came_in') . ' = 0 OR ' . $db->quoteName('mr.came_in') . ' = 1)'
            );
        }

        $db->setQuery($query);
        return (int) $db->loadResult();
    }
}
