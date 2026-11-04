<?php
/**
 * Native Joomla 5/6 sports-types list and administrator statistics model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 sports-types list and administrator statistics model.
 */
final class SportstypesModel extends SportsManagementListModel
{
    public static string $setError = '';
    public string $_identifier = 'sportstypes';
    public int $count_result = 0;

    /**
     * Register the administrator sports-type list's sortable and filterable columns.
     * Initialises Joomla's native list model with sports-category name, icon,
     * publication and audit fields while keeping explicit MVC factory options.
     * @param array $config Native list-model configuration.
     * @param MVCFactoryInterface|null $factory Joomla MVC factory or null.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            's.name', 'name',
            's.icon', 'icon',
            's.sportsart', 'sportsart',
            's.id', 'id',
            's.ordering', 'ordering',
            's.published', 'published', 'state',
            's.modified', 'modified',
            's.modified_by', 'modified_by',
            's.checked_out', 'checked_out',
            's.checked_out_time', 'checked_out_time',
        ];

        parent::__construct($config, $factory);
    }

    /**
     * Load the stored sport-category list search and publication filters.
     * Restores Joomla administrator user state and delegates default sort
     * handling to the base list model.
     * @param string $ordering Default sports-category ordering.
     * @param string $direction Default ascending/descending direction.
     * @return void
     */
    protected function populateState($ordering = 's.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
    }

    /**
     * Build the administrator sport-types listing with safe filters.
     * Queries sport-category metadata, applies search and publication filters
     * and sorts using the model's supported Joomla list columns.
     * @return \Joomla\Database\DatabaseQuery Filtered sports-type list query.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.name'),
                $db->quoteName('s.icon'),
                $db->quoteName('s.sportsart'),
                $db->quoteName('s.eventtime'),
                $db->quoteName('s.published'),
                $db->quoteName('s.ordering'),
                $db->quoteName('s.modified'),
                $db->quoteName('s.modified_by'),
                $db->quoteName('s.checked_out'),
                $db->quoteName('s.checked_out_time'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_sports_type', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('s.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('s.name') . ') LIKE LOWER(:sportstypeSearch)')
                ->bind(':sportstypeSearch', $token, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('s.published') . ' = :sportstypePublished')
                ->bind(':sportstypePublished', $published, ParameterType::INTEGER);
        }

        $map = [
            's.name' => $db->quoteName('s.name'),
            'name' => $db->quoteName('s.name'),
            's.icon' => $db->quoteName('s.icon'),
            'icon' => $db->quoteName('s.icon'),
            's.sportsart' => $db->quoteName('s.sportsart'),
            'sportsart' => $db->quoteName('s.sportsart'),
            's.published' => $db->quoteName('s.published'),
            'published' => $db->quoteName('s.published'),
            'state' => $db->quoteName('s.published'),
            's.ordering' => $db->quoteName('s.ordering'),
            'ordering' => $db->quoteName('s.ordering'),
            's.id' => $db->quoteName('s.id'),
            'id' => $db->quoteName('s.id'),
            's.modified' => $db->quoteName('s.modified'),
            'modified' => $db->quoteName('s.modified'),
            's.modified_by' => $db->quoteName('s.modified_by'),
            'modified_by' => $db->quoteName('s.modified_by'),
            's.checked_out' => $db->quoteName('s.checked_out'),
            'checked_out' => $db->quoteName('s.checked_out'),
            's.checked_out_time' => $db->quoteName('s.checked_out_time'),
            'checked_out_time' => $db->quoteName('s.checked_out_time'),
        ];

        $ordering = (string) $this->getState('list.ordering', 's.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['s.name']) . ' ' . $direction);

        return $query;
    }

    /**
     * Return the available sport categories for Joomla select controls.
     * Reads sport ID, translated display name and icon metadata
     * ordered alphabetically for use in administrator dropdowns.
     * @return array<int,object> Sport category value/text options.
     */
    public function getSportsTypes(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('name', 'text'),
                $db->quoteName('icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);
        $result = $db->loadObjectList() ?: [];

        foreach ($result as $sportstype) {
            $sportstype->name = Text::_((string) $sportstype->name);
        }

        return $result;
    }

    /**
     * Count competition projects assigned to one sports category.
     * Uses a bound positive sports-type ID and returns zero for
     * an absent or invalid sport selection.
     * @param mixed $sporttypeid Sport identifier whose projects are counted.
     * @return int Number of projects for that sport.
     */
    public function getProjectsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = :projectSportsTypeId')
            ->bind(':projectSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Count all playgrounds independently of a selected sports type.
     * Provides the legacy global venue total for the sport statistics dashboard.
     * @param mixed $sporttypeid Unused legacy sports-type selector.
     * @return int Total venue records across the component.
     */
    public function getPlaygroundsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_playground');
    }

    /**
     * Count all leagues irrespective of the selected sports type.
     * Preserves the dashboard's global league total, distinct from
     * the project-linked sports-filtered league count.
     * @param mixed $sporttypeid Unused compatibility parameter.
     * @return int Total leagues across sports.
     */
    public function getLeaguesOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_league');
    }

    /**
     * Count all registered people in the component.
     * Returns the global people total used by the sports statistics overview.
     * @param mixed $sporttypeid Unused compatibility parameter.
     * @return int Total person records.
     */
    public function getPersonsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_person');
    }

    /**
     * Count all clubs without restricting to a sports type.
     * Supplies the global club total for administrator statistics.
     * @param mixed $sporttypeid Unused compatibility parameter.
     * @return int Total clubs in the component.
     */
    public function getClubsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_club');
    }

    /**
     * Count distinct leagues used by projects for one sports type.
     * Filters projects by a bound sports-category identifier and
     * counts distinct linked league IDs instead of all stored leagues.
     * @param mixed $sporttypeid Sport identifier to count.
     * @return int Number of leagues represented by that sport.
     */
    public function getLeaguesCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $db->quoteName('p.league_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = :leagueSportsTypeId')
            ->where($db->quoteName('p.league_id') . ' > 0')
            ->bind(':leagueSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Count all stored seasons irrespective of the sport.
     * Keeps the legacy administrator overview's global season total.
     * @param mixed $sporttypeid Unused compatibility parameter.
     * @return int Total season records.
     */
    public function getSeasonsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_season');
    }

    /**
     * Count the unique seasons associated with a particular sport.
     * Queries distinct project season IDs using a bound sports-category
     * identifier so other sports do not inflate the result.
     * @param mixed $sporttypeid Sports category to filter.
     * @return int Distinct seasons containing projects for the sport.
     */
    public function getSeasonsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $db->quoteName('p.season_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = :seasonSportsTypeId')
            ->where($db->quoteName('p.season_id') . ' > 0')
            ->bind(':seasonSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Count project-team assignments in the selected sport.
     * Delegates to the shared project relation counter with the
     * trusted project-team association table and join criteria.
     * @param mixed $sporttypeid Sports type whose team assignments are counted.
     * @return int Project-team relation count.
     */
    public function getProjectTeamsCount($sporttypeid = 0): int
    {
        return $this->countProjectRelation(
            '#__sportsmanagement_project_team',
            'ptt',
            'ptt.project_id = p.id',
            $sporttypeid
        );
    }

    /**
     * Count project-team player assignments belonging to a sport.
     * Joins the project, team and roster relations and filters
     * through a bound sports type before counting assignments.
     * @param mixed $sporttypeid Sports category to count.
     * @return int Number of relevant team-player relations.
     */
    public function getProjectTeamsPlayersCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'ptt')
                . ' ON ' . $db->quoteName('ptt.project_id') . ' = ' . $db->quoteName('p.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('ptt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1')
                . ' ON ' . $db->quoteName('tp1.team_id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = :teamPlayerSportsTypeId')
            ->bind(':teamPlayerSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Count divisions belonging to projects for one sports type.
     * Uses the shared project-scoped relation counter for the
     * trusted division table and project relation column.
     * @param mixed $sporttypeid Selected sports category.
     * @return int Number of matching project divisions.
     */
    public function getProjectDivisionsCount($sporttypeid = 0): int
    {
        return $this->countProjectRelation(
            '#__sportsmanagement_division',
            'd',
            'd.project_id = p.id',
            $sporttypeid
        );
    }

    /**
     * Count project rounds linked to the chosen sports type.
     * Delegates to the trusted project relation counting helper
     * so rounds in unrelated sports remain outside the total.
     * @param mixed $sporttypeid Selected sport category.
     * @return int Matching competition rounds.
     */
    public function getProjectRoundsCount($sporttypeid = 0): int
    {
        return $this->countProjectRelation(
            '#__sportsmanagement_round',
            'r',
            'r.project_id = p.id',
            $sporttypeid
        );
    }

    /**
     * Count fixtures assigned to projects of the selected sport.
     * Joins matches through rounds to sports-scoped projects
     * using a bound sports-category ID.
     * @param mixed $sporttypeid Sports type to count.
     * @return int Number of matching fixtures.
     */
    public function getProjectMatchesCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = :matchSportsTypeId')
            ->bind(':matchSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Group a sport's fixture events by event type and display name.
     * Uses project and round relations to scope event rows,
     * groups the events for dashboard charts and returns their
     * names and occurrence totals.
     * @param mixed $sporttypeid Sports category for match events.
     * @return array<int,object> Grouped event-type summary rows.
     */
    public function getProjectMatchesEventsNameCount($sporttypeid = 0): array
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'COUNT(' . $db->quoteName('me.id') . ') AS ' . $db->quoteName('total'),
                $db->quoteName('me.event_type_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_eventtype', 'et')
                . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = :eventNameSportsTypeId')
            ->bind(':eventNameSportsTypeId', $sporttypeId, ParameterType::INTEGER)
            ->group([
                $db->quoteName('me.event_type_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->order($db->quoteName('et.name') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Count event records attached to fixtures of a sports type.
     * Joins match events through the match/round/project hierarchy
     * so event totals exclude unrelated competitions.
     * @param mixed $sporttypeid Sports category for event counting.
     * @return int Number of matching match-event rows.
     */
    public function getProjectMatchesEventsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = :eventCountSportsTypeId')
            ->bind(':eventCountSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Count match statistic records within one sports type.
     * Filters joined match statistics by project sports-category
     * to avoid mixing separate sports in the dashboard.
     * @param mixed $sporttypeid Sport category for match statistics.
     * @return int Number of matching statistic rows.
     */
    public function getProjectMatchesStatsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match_statistic', 'ms'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('ms.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = :statSportsTypeId')
            ->bind(':statSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Load the total row count from an approved component table.
     * Builds a basic COUNT query and delegates execution/error
     * handling to the shared count loader; only trusted call sites
     * supply the table identifier.
     * @param string $table Trusted table name to count.
     * @return int Total table rows or zero when the query fails.
     */
    private function countTable(string $table): int
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($table));

        return $this->loadCount($query);
    }

    /**
     * Count records in a trusted project-linked table for a sport.
     * Joins the relation to the project table with a fixed caller-provided
     * ON clause and binds the sports-category identifier.
     * @param string $table Trusted project relation table.
     * @param string $alias SQL alias for the relation table.
     * @param string $join Trusted relation-to-project join expression.
     * @param mixed $sporttypeid Sport category whose relations are counted.
     * @return int Project-related records for the selected sport.
     */
    private function countProjectRelation(string $table, string $alias, string $join, $sporttypeid): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName($table, $alias) . ' ON ' . $join
            )
            ->where($db->quoteName('p.sports_type_id') . ' = :relationSportsTypeId')
            ->bind(':relationSportsTypeId', $sporttypeId, ParameterType::INTEGER);

        return $this->loadCount($query);
    }

    /**
     * Execute a COUNT query and safely expose the resulting integer.
     * Uses Joomla DatabaseInterface and resets count_result to zero
     * when the database raises an exception.
     * @param mixed $query Database query created by the counting methods.
     * @return int Nonnegative count result or zero on failure.
     */
    private function loadCount($query): int
    {
        try {
            $db = $this->getDatabase();
            $db->setQuery($query);
            $this->count_result = (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->count_result = 0;
        }

        return $this->count_result;
    }
}
