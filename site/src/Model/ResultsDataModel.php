<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 reader for the regular results view.
 *
 * Match edit/ACL behaviour remains in the legacy Results model for now.
 * Project, round, team, event, match and referee-position reads live here so
 * they use the selected SportsManagement database through SportsManagementModel.
 */
final class ResultsDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * Override URL-derived context for compatibility callers such as modules.
     */
    public function setProjectId(int $projectId): void
    {
        $this->projectId = max(0, $projectId);
    }

    public function setDivisionId(int $divisionId): void
    {
        $this->divisionId = max(0, $divisionId);
    }

    public function getRoundCode(int $roundId): string
    {
        if ($roundId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('roundcode'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId);

        if ($this->projectId > 0) {
            $query->where($db->quoteName('project_id') . ' = ' . $this->projectId);
        }

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    public function getRoundOptions(string $ordering = 'ASC'): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $matchdayName = Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME');
        $query = $db->getQuery(true)
            ->select([
                "CONCAT_WS(':', id, alias) AS slug",
                $db->quoteName('id', 'value'),
                "CASE LENGTH(name) WHEN 0 THEN CONCAT(" . $db->quote($matchdayName) . ", ' ', id) ELSE CONCAT(name, ' (', round_date_first, ')') END AS text",
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('roundcode') . ' ' . $direction);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** Preserve the legacy id-keyed event list used by results templates. */
    public function getProjectEvents(int $positionId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_eventtype', 'pet')
                . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ]);

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = ' . $positionId);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getProjectTeamsIndexed(int $divisionId = 0): array
    {
        $teams = [];

        foreach ($this->getProjectTeams($divisionId) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

        return $teams;
    }

    /**
     * Build the regular results match query without legacy global DB helpers.
     *
     * The returned query deliberately does not carry a limit. Callers that use
     * Joomla list pagination can apply their own start/limit to the same query.
     */
    public function getResultsQuery(
        int $roundId = 0,
        int $divisionId = 0,
        $params = null,
        int $teamId = 0
    ) {
        $project = $this->getProject();
        if (!$project) {
            return false;
        }

        if ($roundId <= 0) {
            $roundId = (int) ($project->current_round ?? 0);
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m') . '.*',
                'DATE_FORMAT(' . $db->quoteName('m.time_present') . ', "%H:%i") AS ' . $db->quoteName('time_present'),
                $db->quoteName('playground.name', 'playground_name'),
                $db->quoteName('playground.short_name', 'playground_short_name'),
                $db->quoteName('playground.address', 'playground_address'),
                $db->quoteName('playground.zipcode', 'playground_zipcode'),
                $db->quoteName('playground.city', 'playground_city'),
                $db->quoteName('pt1.project_id'),
                $db->quoteName('d1.name', 'divhome'),
                $db->quoteName('d1.id', 'divhomeid'),
                $db->quoteName('d2.name', 'divaway'),
                $db->quoteName('d2.id', 'divawayid'),
                "CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) ELSE m.id END AS slug",
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
                "CONCAT_WS(':', playground.id, playground.alias) AS playground_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd1') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d1.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd2') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d2.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('r.project_id') . ' = ' . (int) $project->id)
            ->order([
                $db->quoteName('m.match_date') . ' ASC',
                $db->quoteName('m.match_number') . ' ASC',
            ]);

        if ($params && method_exists($params, 'get')) {
            $pictureField = $this->normaliseField(
                (string) $params->get('picture_type', 'logo_small'),
                ['logo_small', 'logo_middle', 'logo_big'],
                'logo_small'
            );
            $teamNameField = $this->normaliseField(
                (string) $params->get('team_names', 'name'),
                ['name', 'short_name', 'middle_name'],
                'name'
            );

            $query->select([
                $db->quoteName('c1.' . $pictureField, 'logohome'),
                $db->quoteName('c2.' . $pictureField, 'logoaway'),
                $db->quoteName('t1.' . $teamNameField, 'teamhome'),
                $db->quoteName('t2.' . $teamNameField, 'teamaway'),
            ]);

            if ((int) $params->get('use_fav', 0) === 1) {
                $favoriteIds = array_values(array_unique(array_filter(
                    array_map('intval', explode(',', (string) ($project->fav_team ?? ''))),
                    static fn (int $id): bool => $id > 0
                )));
                if ($favoriteIds) {
                    $favorites = implode(',', $favoriteIds);
                    $query->where('(' . $db->quoteName('t1.id') . ' IN (' . $favorites . ') OR ' . $db->quoteName('t2.id') . ' IN (' . $favorites . '))');
                }
            }

            if ((int) $params->get('project_season', 0) === 0) {
                $query->where($db->quoteName('r.id') . ' = ' . $roundId);
            }
        } else {
            $query->where($db->quoteName('r.id') . ' = ' . $roundId);
        }

        if ($teamId > 0) {
            $query->where('(' . $db->quoteName('st1.team_id') . ' = ' . $teamId . ' OR ' . $db->quoteName('st2.team_id') . ' = ' . $teamId . ')');
        }

        if ($divisionId > 0) {
            $query->where(
                '(' . $db->quoteName('d1.id') . ' = ' . $divisionId
                . ' OR ' . $db->quoteName('d1.parent_id') . ' = ' . $divisionId
                . ' OR ' . $db->quoteName('d2.id') . ' = ' . $divisionId
                . ' OR ' . $db->quoteName('d2.parent_id') . ' = ' . $divisionId . ')'
            );
        }

        return $query;
    }

    /** Preserve the legacy id-keyed match result contract. */
    public function getResultsRows(
        int $roundId = 0,
        int $divisionId = 0,
        $params = null,
        int $teamId = 0,
        int $offset = 0,
        int $limit = 0
    ): array {
        $query = $this->getResultsQuery($roundId, $divisionId, $params, $teamId);
        if (!$query) {
            return [];
        }

        try {
            $this->getDatabase()->setQuery($query, max(0, $offset), max(0, $limit));
            return $this->getDatabase()->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Return project position options in the same shape as the legacy
     * sportsmanagementModelMatch::getProjectPositionsOptions() helper.
     */
    public function getProjectPositionsOptions(
        int $positionId = 0,
        int $personType = 1,
        int $projectId = 0
    ): array {
        $projectId = $projectId > 0 ? $projectId : $this->projectId;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('pos.id', 'posid'),
                $db->quoteName('pos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pos.persontype') . ' = ' . $personType)
            ->order($db->quoteName('pos.ordering') . ' ASC');

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = ' . $positionId);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    private function normaliseField(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
