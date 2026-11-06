<?php
/**
 * Native Joomla 5/6 read model for the frontend player view.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
use Throwable;

/**
 * Native Joomla 5/6 read model for the player site view.
 *
 * The larger legacy player model still owns statistics calculations and match
 * history aggregation. This class contains the read-only person/team/history
 * queries so they use the component database resolver and no global DB state.
 */
final class PlayerModel extends SportsManagementProjectModel
{
    private int $personId = 0;
    private int $teamPlayerId = 0;

    /**
     * Initialise the selected player and team-player identifiers.
     * @param array $config Joomla site model options.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->personId = max(0, $input->getInt('pid', 0));
        $this->teamPlayerId = max(0, $input->getInt('pt', 0));
    }

    /**
     * Return the current player profile person identifier.
     * @return int Selected person ID or zero.
     */
    public function getPersonId(): int
    {
        return $this->personId;
    }

    /**
     * Return the selected season team-player relation ID.
     * @return int Team-player relation ID or zero.
     */
    public function getTeamPlayerId(): int
    {
        return $this->teamPlayerId;
    }

    /**
     * Find the staff relation associated with a person and project.
     * @param int|null $projectId Project ID, or current project when omitted.
     * @param int|null $personId Person ID, or current person when omitted.
     * @return object|null Team staff relation or null when unavailable.
     */
    public function getTeamStaff(?int $projectId = null, ?int $personId = null): ?object
    {
        $projectId ??= $this->projectId;
        $personId ??= $this->personId;

        if ($projectId <= 0 || $personId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'tp.*',
                $db->quoteName('pt.project_id'),
                $db->quoteName('pt.team_id'),
                $db->quoteName('pt.notes', 'ptnotes'),
                $db->quoteName('pt.picture', 'team_picture'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('pos.picture', 'position_image'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('tp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pt.project_id') . ' = :staffProjectId')
            ->where($db->quoteName('tp.person_id') . ' = :staffPersonId')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('tp.persontype') . ' = 2')
            ->bind(':staffProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':staffPersonId', $personId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return null;
        }
    }

    /**
     * Fetch matching player and team assignment records.
     * @param int $projectId Project ID or zero for current project.
     * @param int $personId Person ID or zero for current person.
     * @param int $teamPlayerId Optional exact team-player relation ID.
     * @return array<int,object> Matching player assignments.
     */
    public function getTeamPlayer(int $projectId = 0, int $personId = 0, int $teamPlayerId = 0): array
    {
        $projectId = $projectId > 0 ? $projectId : $this->projectId;
        $personId = $personId > 0 ? $personId : $this->personId;
        $teamPlayerId = $teamPlayerId > 0 ? $teamPlayerId : $this->teamPlayerId;

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'tp.*',
                $db->quoteName('pt.project_id'),
                $db->quoteName('pt.team_id'),
                $db->quoteName('pt.notes', 'ptnotes'),
                $db->quoteName('pt.picture', 'team_picture'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('pos.picture', 'position_image'),
                $db->quoteName('ps.firstname'),
                $db->quoteName('ps.lastname'),
                $db->quoteName('ps.knvbnr'),
                $db->quoteName('ps.picture', 'ppic'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('tp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'ps') . ' ON ' . $db->quoteName('ps.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->where($db->quoteName('pt.project_id') . ' = :teamPlayerProjectId')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('tp.persontype') . ' = 1')
            ->bind(':teamPlayerProjectId', $projectId, ParameterType::INTEGER);

        if ($personId > 0) {
            $query->where($db->quoteName('tp.person_id') . ' = :teamPlayerPersonId')
                ->bind(':teamPlayerPersonId', $personId, ParameterType::INTEGER);
        }

        if ($teamPlayerId > 0) {
            $query->where($db->quoteName('tp.id') . ' = :teamPlayerRowId')
                ->bind(':teamPlayerRowId', $teamPlayerId, ParameterType::INTEGER);
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * List project team memberships for the selected person.
     * @param int|null $projectId Project or current project.
     * @param int|null $personId Person or current person.
     * @return array<int,object> Person's team membership records.
     */
    public function getTeamPlayers(?int $projectId = null, ?int $personId = null): array
    {
        $projectId ??= $this->projectId;
        $personId ??= $this->personId;

        if ($projectId <= 0 || $personId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'tp.*',
                $db->quoteName('pt.project_id'),
                $db->quoteName('pt.team_id'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('pt.picture', 'team_picture'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('pos.picture', 'position_image'),
                $db->quoteName('rinjuryfrom.round_date_first', 'injury_date'),
                $db->quoteName('rinjuryfrom.name', 'rinjury_from'),
                $db->quoteName('rinjuryto.round_date_last', 'injury_end'),
                $db->quoteName('rinjuryto.name', 'rinjury_to'),
                $db->quoteName('rsuspfrom.round_date_first', 'suspension_date'),
                $db->quoteName('rsuspfrom.name', 'rsusp_from'),
                $db->quoteName('rsuspto.round_date_last', 'suspension_end'),
                $db->quoteName('rsuspto.name', 'rsusp_to'),
                $db->quoteName('rawayfrom.round_date_first', 'away_date'),
                $db->quoteName('rawayfrom.name', 'raway_from'),
                $db->quoteName('rawayto.round_date_last', 'away_end'),
                $db->quoteName('rawayto.name', 'raway_to'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pe') . ' ON ' . $db->quoteName('pe.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id') . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person_project_position', 'perpos') . ' ON ' . $db->quoteName('perpos.project_id') . ' = ' . $db->quoteName('p.id') . ' AND ' . $db->quoteName('perpos.person_id') . ' = ' . $db->quoteName('pe.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('perpos.project_position_id') . ' AND ' . $db->quoteName('ppos.project_id') . ' = ' . $db->quoteName('perpos.project_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'rinjuryfrom') . ' ON ' . $db->quoteName('pe.injury_date') . ' = ' . $db->quoteName('rinjuryfrom.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'rinjuryto') . ' ON ' . $db->quoteName('pe.injury_end') . ' = ' . $db->quoteName('rinjuryto.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'rsuspfrom') . ' ON ' . $db->quoteName('pe.suspension_date') . ' = ' . $db->quoteName('rsuspfrom.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'rsuspto') . ' ON ' . $db->quoteName('pe.suspension_end') . ' = ' . $db->quoteName('rsuspto.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'rawayfrom') . ' ON ' . $db->quoteName('pe.away_date') . ' = ' . $db->quoteName('rawayfrom.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'rawayto') . ' ON ' . $db->quoteName('pe.away_end') . ' = ' . $db->quoteName('rawayto.id'))
            ->where($db->quoteName('pt.project_id') . ' = :teamPlayersProjectId')
            ->where($db->quoteName('tp.person_id') . ' = :teamPlayersPersonId')
            ->where($db->quoteName('pe.id') . ' = :teamPlayersPersonRowId')
            ->where($db->quoteName('p.published') . ' = 1')
            ->bind(':teamPlayersProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':teamPlayersPersonId', $personId, ParameterType::INTEGER)
            ->bind(':teamPlayersPersonRowId', $personId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('projectteam_id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Load the player's historical teams and playing positions.
     * @param int $sportsTypeId Optional sport-type filter.
     * @param string $order History order, ASC or DESC.
     * @param int $personType Person type filter.
     * @return array<int,object> Historical player assignments.
     */
    public function getPlayerHistory(int $sportsTypeId = 0, string $order = 'ASC', int $personType = 1): array
    {
        if ($this->personId <= 0) {
            return [];
        }

        $personId = $this->personId;
        $personType = max(1, $personType);
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pr.id', 'pid'),
                $db->quoteName('pr.firstname'),
                $db->quoteName('pr.lastname'),
                "CONCAT_WS(':', pr.id, pr.alias) AS person_slug",
                $db->quoteName('tp.person_id'),
                $db->quoteName('tp.id', 'tpid'),
                $db->quoteName('tp.project_position_id'),
                $db->quoteName('tp.market_value'),
                $db->quoteName('tp.market_text'),
                $db->quoteName('p.name', 'project_name'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('s.id', 'season_id'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.id', 'team_id'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('pos.id', 'posID'),
                $db->quoteName('pt.id', 'ptid'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('pt.picture', 'team_picture'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('pos.picture', 'position_image'),
                $db->quoteName('tp.picture', 'season_picture'),
                $db->quoteName('p.picture', 'project_picture'),
                $db->quoteName('l.picture', 'league_picture'),
                $db->quoteName('p.game_regular_time'),
                $db->quoteName('p.add_time'),
                $db->quoteName('c.logo_big', 'club_picture'),
                $db->quoteName('p.league_id'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('p.season_id', 'pro_season_id'),
                $db->quoteName('p.league_id', 'pro_league_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id') . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person_project_position', 'perpos') . ' ON ' . $db->quoteName('perpos.project_id') . ' = ' . $db->quoteName('p.id') . ' AND ' . $db->quoteName('perpos.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('perpos.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pr.id') . ' = :historyPersonId')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('perpos.published') . ' = 1')
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('tp.persontype') . ' = :historyPersonType')
            ->bind(':historyPersonId', $personId, ParameterType::INTEGER)
            ->bind(':historyPersonType', $personType, ParameterType::INTEGER);

        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('p.sports_type_id') . ' = :historySportsTypeId')
                ->bind(':historySportsTypeId', $sportsTypeId, ParameterType::INTEGER);
        }

        // Preserve the legacy view's effective ordering, which ignored $order.
        $query->order($db->quoteName('s.name') . ' DESC');

        try {
            $db->setQuery($query);
            $history = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        foreach ($history as $row) {
            $leagueId = (int) $row->pro_league_id;
            $seasonId = (int) $row->pro_season_id;
            $logoQuery = $db->createQuery()
                ->select($db->quoteName('logo_big'))
                ->from($db->quoteName('#__sportsmanagement_league_logos'))
                ->where($db->quoteName('league_id') . ' = :historyLeagueId')
                ->where($db->quoteName('season_id') . ' = :historySeasonId')
                ->bind(':historyLeagueId', $leagueId, ParameterType::INTEGER)
                ->bind(':historySeasonId', $seasonId, ParameterType::INTEGER);
            $db->setQuery($logoQuery, 0, 1);
            $seasonLogo = $db->loadResult();

            if ($seasonLogo && preg_match('/placeholder/i', (string) ($row->project_picture ?? ''))) {
                $row->project_picture = $seasonLogo;
            }
        }

        return $history;
    }

    /**
     * Find event types applicable to the player's historical positions.
     * @param int $sportsTypeId Optional sports-type filter.
     * @return array<int,object> Available event types.
     */
    public function getAllEvents(int $sportsTypeId = 0): array
    {
        $history = $this->getPlayerHistory($sportsTypeId, 'ASC', 1);
        $positionIds = [];

        foreach ($history as $row) {
            $positionId = (int) ($row->posID ?? 0);
            if ($positionId > 0) {
                $positionIds[$positionId] = $positionId;
            }
        }

        if ($positionIds === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('DISTINCT et.*, pet.ordering')
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_eventtype', 'pet') . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id'))
            ->whereIn($db->quoteName('pet.position_id'), array_values($positionIds), ParameterType::INTEGER)
            ->where($db->quoteName('et.published') . ' = 1')
            ->order($db->quoteName('pet.ordering') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Report a caught database error through Joomla site messages.
     * @param Throwable $e Database or query exception.
     * @return void
     */
    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
