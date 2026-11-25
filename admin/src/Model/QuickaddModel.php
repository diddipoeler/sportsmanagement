<?php
/**
 * Native Joomla 5/6 helper model for administrator quick-add lookups.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 helper model for the administrator quick-add lookups.
 *
 * The public method names retain the historic API used by integrations while
 * all database access goes through Joomla's query builder and component DB.
 */
final class QuickaddModel extends SportsManagementListModel
{
    public string $_identifier = 'quickadd';

    private int $quickTotal = 0;

    /**
     * Find published persons eligible for a new player assignment.
     * Excludes persons already present in team_player for the specified project team using a bound NOT EXISTS query.
     * @param mixed $searchterm Name fragment or numeric person ID.
     * @param mixed $projectteam_id Target project-team identifier.
     * @param mixed $searchinfo Optional person information filter.
     * @return array Unassigned player records for the current page.
     */
    public function getNotAssignedPlayers($searchterm, $projectteam_id, $searchinfo = null): array
    {
        $projectTeamId = (int) $projectteam_id;
        $db = $this->getDatabase();
        $query = $this->personSearchQuery($db, (string) $searchterm, $searchinfo)
            ->select($db->quoteName('pl.id', 'id2'));
        $subquery = $db->createQuery()
            ->select($db->quoteName('tp.person_id'))
            ->from($db->quoteName('#__sportsmanagement_team_player', 'tp'))
            ->where($db->quoteName('tp.projectteam_id') . ' = :assignedPlayerProjectTeamId')
            ->where($db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pl.id'));
        $query->where('NOT EXISTS (' . $subquery . ')')
            ->bind(':assignedPlayerProjectTeamId', $projectTeamId, ParameterType::INTEGER);

        return $this->loadQuickRows($db, $query, true);
    }

    /**
     * Find published persons who are not already staff for a project team.
     * Reuses the parameterised person query and excludes existing team_staff links for the requested project team.
     * @param mixed $searchterm Name fragment or numeric person ID.
     * @param mixed $projectteam_id Target project-team identifier.
     * @param mixed $searchinfo Optional additional information filter.
     * @return array Unassigned staff records.
     */
    public function getNotAssignedStaff($searchterm, $projectteam_id, $searchinfo = null): array
    {
        $projectTeamId = (int) $projectteam_id;
        $db = $this->getDatabase();
        $query = $this->personSearchQuery($db, (string) $searchterm, $searchinfo);
        $subquery = $db->createQuery()
            ->select($db->quoteName('ts.person_id'))
            ->from($db->quoteName('#__sportsmanagement_team_staff', 'ts'))
            ->where($db->quoteName('ts.projectteam_id') . ' = :assignedStaffProjectTeamId')
            ->where($db->quoteName('ts.person_id') . ' = ' . $db->quoteName('pl.id'));
        $query->where('NOT EXISTS (' . $subquery . ')')
            ->bind(':assignedStaffProjectTeamId', $projectTeamId, ParameterType::INTEGER);

        return $this->loadQuickRows($db, $query, true);
    }

    /**
     * Find published persons not already registered as project referees.
     * Applies the common person search and a bound NOT EXISTS condition on project_referee relations.
     * @param mixed $searchterm Name fragment or numeric person ID.
     * @param mixed $projectid Target project identifier.
     * @param mixed $searchinfo Optional additional information filter.
     * @return array Unassigned referee records.
     */
    public function getNotAssignedReferees($searchterm, $projectid, $searchinfo = null): array
    {
        $projectId = (int) $projectid;
        $db = $this->getDatabase();
        $query = $this->personSearchQuery($db, (string) $searchterm, $searchinfo);
        $subquery = $db->createQuery()
            ->select($db->quoteName('pr.person_id'))
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->where($db->quoteName('pr.project_id') . ' = :assignedRefereeProjectId')
            ->where($db->quoteName('pr.person_id') . ' = ' . $db->quoteName('pl.id'));
        $query->where('NOT EXISTS (' . $subquery . ')')
            ->bind(':assignedRefereeProjectId', $projectId, ParameterType::INTEGER);

        return $this->loadQuickRows($db, $query, true);
    }

    /**
     * Search for teams that have not yet been added to a project.
     * Matches name variants and optional numeric team ID with bound parameters, excludes project_team links and sorts by name.
     * @param mixed $searchterm Name fragment or numeric team ID.
     * @param mixed $projectid Target project identifier.
     * @return array Unassigned team records for the requested page.
     */
    public function getNotAssignedTeams($searchterm, $projectid): array
    {
        $projectId = (int) $projectid;
        $db = $this->getDatabase();
        $needle = '%' . $db->escape(trim((string) $searchterm), true) . '%';
        $numericId = filter_var($searchterm, FILTER_VALIDATE_INT);
        $search = [
            'LOWER(' . $db->quoteName('t.name') . ') LIKE LOWER(:teamNameSearch)',
            'LOWER(' . $db->quoteName('t.alias') . ') LIKE LOWER(:teamAliasSearch)',
            'LOWER(' . $db->quoteName('t.short_name') . ') LIKE LOWER(:teamShortSearch)',
            'LOWER(' . $db->quoteName('t.middle_name') . ') LIKE LOWER(:teamMiddleSearch)',
        ];

        if ($numericId !== false) {
            $search[] = $db->quoteName('t.id') . ' = :teamNumericId';
        }

        $subquery = $db->createQuery()
            ->select($db->quoteName('pt.team_id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->where($db->quoteName('pt.project_id') . ' = :assignedTeamProjectId');
        $query = $db->createQuery()
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where('(' . implode(' OR ', $search) . ')')
            ->where($db->quoteName('t.id') . ' NOT IN (' . $subquery . ')')
            ->bind(':teamNameSearch', $needle, ParameterType::STRING)
            ->bind(':teamAliasSearch', $needle, ParameterType::STRING)
            ->bind(':teamShortSearch', $needle, ParameterType::STRING)
            ->bind(':teamMiddleSearch', $needle, ParameterType::STRING)
            ->bind(':assignedTeamProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name') . ' ASC');

        if ($numericId !== false) {
            $numericId = (int) $numericId;
            $query->bind(':teamNumericId', $numericId, ParameterType::INTEGER);
        }

        return $this->loadQuickRows($db, $query, false);
    }

    /**
     * Preserve the historical quick-add behaviour: when only a name is given,
     * create the person record. Team assignment itself was never implemented in
     * the legacy method and therefore remains outside this compatibility API.
     * Requires component creation permission and validates one to three
     * name parts before creating a new person.
     *
     * @param mixed $projectteam_id Historical project-team identifier (unused).
     * @param mixed $personid Existing person identifier, or zero to create one.
     * @param mixed $name Name of a new person when no person ID is provided.
     * @return bool Whether the selected or newly created person is available.
     * @throws \RuntimeException If component creation permission is denied.
     */
    public function addPlayer($projectteam_id, $personid, $name = null): bool
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.create', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Reject malformed IDs rather than truncating strings or casting
        // array input to integers (which may silently select another person).
        if ($personid === null || $personid === '') {
            $personId = 0;
        } elseif ((is_int($personid) || is_string($personid))
            && preg_match('/^[0-9]+$/D', (string) $personid)) {
            $validatedId = filter_var((string) $personid, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0],
            ]);

            if ($validatedId === false) {
                $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));

                return false;
            }

            $personId = $validatedId;
        } else {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));

            return false;
        }

        if ($personId <= 0 && (!is_string($name) || trim($name) === '' || strlen($name) > 255)) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));

            return false;
        }

        if ($personId <= 0) {
            $parts = preg_split('/\s+/', trim($name)) ?: [];

            // The legacy field mapping supports one to three name parts only.
            // Reject unexpected input instead of inserting an empty person.
            if (count($parts) < 1 || count($parts) > 3) {
                $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));

                return false;
            }

            $firstname = '';
            $nickname = '';
            $lastname = '';

            if (count($parts) === 1) {
                $firstname = ucfirst($parts[0]);
                $nickname = $parts[0];
                $lastname = '.';
            } elseif (count($parts) === 2) {
                $firstname = ucfirst($parts[0]);
                $nickname = $parts[1];
                $lastname = ucfirst($parts[1]);
            } elseif (count($parts) === 3) {
                $firstname = ucfirst($parts[0]);
                $nickname = $parts[1];
                $lastname = ucfirst($parts[2]);
            }

            $record = (object) [
                'firstname' => $firstname,
                'nickname' => $nickname,
                'lastname' => $lastname,
                'published' => 1,
            ];
            $db = $this->getDatabase();

            try {
                $db->insertObject('#__sportsmanagement_person', $record);
                $personId = (int) $db->insertid();
            } catch (\Throwable $e) {
                Log::add(
                    'SportsManagement quick-add database failure: ' . $e->getMessage(),
                    Log::ERROR,
                    'com_sportsmanagement'
                );
                $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

                return false;
            }
        }

        if ($personId <= 0) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_FAILED_ADDING_PERSON'));

            return false;
        }

        return true;
    }

    /**
     * Return the unpaginated count of matches from the last quick-add lookup.
     * The count is calculated before list paging; SQL exceptions are logged privately and a generic Joomla error is exposed to administrators.
     * @return int Number of matching records.
     */
    public function getTotal(): int
    {
        return $this->quickTotal;
    }

    /**
     * Create the shared bound SQL query for searching published people.
     * Searches full name, nickname, alias and optional numeric ID, with an additional person-info LIKE filter when provided.
     * @param DatabaseInterface $db Active Joomla database connection.
     * @param string $searchterm Person search fragment or ID.
     * @param mixed $searchinfo Optional SQL LIKE filter value for person information.
     * @return object Joomla query builder with bound search values.
     */
    private function personSearchQuery(DatabaseInterface $db, string $searchterm, $searchinfo)
    {
        $needle = '%' . $db->escape(trim($searchterm), true) . '%';
        $numericId = filter_var($searchterm, FILTER_VALIDATE_INT);
        $search = [
            'LOWER(CONCAT(' . $db->quoteName('pl.firstname') . ", ' ', " . $db->quoteName('pl.lastname') . ')) LIKE LOWER(:personNameSearch)',
            'LOWER(' . $db->quoteName('pl.alias') . ') LIKE LOWER(:personAliasSearch)',
            'LOWER(' . $db->quoteName('pl.nickname') . ') LIKE LOWER(:personNicknameSearch)',
        ];

        if ($numericId !== false) {
            $search[] = $db->quoteName('pl.id') . ' = :personNumericId';
        }

        $query = $db->createQuery()
            ->select($db->quoteName('pl') . '.*')
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->where('(' . implode(' OR ', $search) . ')')
            ->where($db->quoteName('pl.published') . ' = 1')
            ->bind(':personNameSearch', $needle, ParameterType::STRING)
            ->bind(':personAliasSearch', $needle, ParameterType::STRING)
            ->bind(':personNicknameSearch', $needle, ParameterType::STRING);

        if ($numericId !== false) {
            $numericId = (int) $numericId;
            $query->bind(':personNumericId', $numericId, ParameterType::INTEGER);
        }

        if ($searchinfo !== null && trim((string) $searchinfo) !== '') {
            $searchInfo = (string) $searchinfo;
            $query->where($db->quoteName('pl.info') . ' LIKE :personInfoSearch')
                ->bind(':personInfoSearch', $searchInfo, ParameterType::STRING);
        }

        return $query;
    }

    /**
     * Load and count a paginated quick-add result set.
     * Applies validated person ordering if needed, stores the full count, uses Joomla list paging and returns an empty array after query errors.
     * @param DatabaseInterface $db Active database connection.
     * @param object $query Parameterised query to execute.
     * @param bool $personOrdering Whether person-column ordering applies.
     * @return array Objects for the current page, empty on failure.
     */
    private function loadQuickRows(DatabaseInterface $db, $query, bool $personOrdering): array
    {
        if ($personOrdering) {
            [$ordering, $direction] = $this->personOrdering();
            $query->order($db->quoteName($ordering) . ' ' . $direction);

            if ($ordering !== 'pl.lastname') {
                $query->order($db->quoteName('pl.lastname') . ' ASC');
            }
        }

        $countQuery = clone $query;
        $countQuery->clear('select')->clear('order')->select('COUNT(*)');

        try {
            $db->setQuery($countQuery);
            $this->quickTotal = (int) $db->loadResult();

            $start = max(0, (int) $this->getState('list.start', $this->getState('limitstart', 0)));
            $limit = max(0, (int) $this->getState('list.limit', $this->getState('limit', 0)));
            $db->setQuery($query, $start, $limit);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Log::add(
                    'SportsManagement quick-add database failure: ' . $e->getMessage(),
                    Log::ERROR,
                    'com_sportsmanagement'
                );
                $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
            $this->quickTotal = 0;

            return [];
        }
    }

    /**
     * Resolve the administrator's permitted person-list ordering.
     * Reads stored sorting preferences, restricts the column to known fields and allows only ASC or DESC direction.
     * @return array{0:string,1:string} Safe column and direction for SQL ORDER BY.
     */
    private function personOrdering(): array
    {
        $app = $this->administratorApplication();
        $requested = (string) $app->getUserStateFromRequest(
            'com_sportsmanagement.pl_filter_order',
            'filter_order',
            'pl.lastname',
            'cmd'
        );
        $direction = strtoupper((string) $app->getUserStateFromRequest(
            'com_sportsmanagement.pl_filter_order_Dir',
            'filter_order_Dir',
            'ASC',
            'word'
        ));
        $allowed = [
            'pl.lastname',
            'pl.firstname',
            'pl.nickname',
            'pl.alias',
            'pl.id',
        ];

        return [
            in_array($requested, $allowed, true) ? $requested : 'pl.lastname',
            $direction === 'DESC' ? 'DESC' : 'ASC',
        ];
    }
}
