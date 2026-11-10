<?php
/**
 * Native Joomla 5/6 frontend matrix model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
use stdClass;
use Throwable;

/** Joomla 5/6 model for matrix views. */
final class MatrixModel extends SportsManagementProjectModel
{
    public static int $divisionid = 0;
    public static int $roundid = 0;
    public static int $projectid = 0;
    public static int $cfg_which_database = 0;

    /**
     * Initialise the matrix with project, division and round context.
     * @param array $config Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$divisionid = $this->divisionId;
        self::$roundid = max(0, $input->getInt('r', 0));
        self::$projectid = $this->projectId;
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    /**
     * Read the selected or explicitly requested division.
     * @param int|null $divisionId Division identifier or current selection.
     * @return object|null Division record.
     */
    public function getDivision(?int $divisionId = null): ?object
    {
        return parent::getDivision($divisionId ?? self::$divisionid);
    }

    /**
     * Read the selected round only when it belongs to the active project.
     *
     * The old query filtered by the globally unique round ID alone, so a
     * crafted matrix URL could display another project's round metadata.
     * Require both a positive selected project and a matching project ID
     * in the parameter-bound Joomla query. Missing or unrelated rounds
     * return null without exposing information from another project.
     *
     * @return object|null Project-owned round record, or null.
     */
    public function getRound(): ?object
    {
        if (self::$roundid <= 0 || $this->projectId <= 0) {
            return null;
        }

        $roundId = self::$roundid;
        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :roundId')
            ->where($db->quoteName('project_id') . ' = :matrixRoundProjectId')
            ->bind(':roundId', $roundId, ParameterType::INTEGER)
            ->bind(':matrixRoundProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * List published divisions in the active project.
     * @return array<int,object> Project divisions.
     */
    public function getDivisions(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'd.*',
                "CONCAT_WS(':', d.id, d.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = :divisionProjectId')
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER)
            ->where($db->quoteName('d.published') . ' = 1')
            ->order([
                $db->quoteName('d.parent_id') . ' ASC',
                $db->quoteName('d.ordering') . ' ASC',
                $db->quoteName('d.name') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** Preserve the legacy id-keyed event list used by results templates. */
    public function getProjectEvents(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            ->where($db->quoteName('ppos.project_id') . ' = :eventProjectId')
            ->bind(':eventProjectId', $projectId, ParameterType::INTEGER)
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ]);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Index project teams by their project-team IDs.
     * @param int|null $divisionId Optional division filter.
     * @return array<int,object> Team records keyed by project-team ID.
     */
    public function getProjectTeamsIndexed(?int $divisionId = null): array
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
     * Group matrix results into home and away team pairings.
     * @param iterable $teams Selected matrix teams.
     * @param iterable $results Corresponding match results.
     * @return mixed Team-pair result matrix.
     */
    public function getRussiaMatrixResults($teams, $results)
    {
        foreach ($teams as $teamRow) {
            foreach ($teams as $teamCol) {
                foreach ($results as $result) {
                    if (
                        (int) $result->projectteam1_id !== (int) $teamRow->projectteamid
                        || (int) $result->projectteam2_id !== (int) $teamCol->projectteamid
                    ) {
                        continue;
                    }

                    $rowId = (int) $teamRow->projectteamid;
                    $colId = (int) $teamCol->projectteamid;
                    $bucket = isset($teamRow->first[$colId]) ? 'second' : 'first';

                    if (!isset($teamRow->{$bucket}) || !is_array($teamRow->{$bucket})) {
                        $teamRow->{$bucket} = [];
                    }
                    if (!isset($teams[$colId]->{$bucket}) || !is_array($teams[$colId]->{$bucket})) {
                        $teams[$colId]->{$bucket} = [];
                    }

                    $home = new stdClass();
                    $home->e1 = $result->e1;
                    $home->e2 = $result->e2;
                    $home->v1 = $result->v1;
                    $home->v2 = $result->v2;
                    $home->decision = $result->decision;
                    $home->rtype = $result->rtype;
                    $home->id = $result->id;
                    $home->roundid = $result->roundid;
                    $home->new_match_id = $result->new_match_id;
                    $home->show_report = $result->show_report;

                    $away = new stdClass();
                    $away->e1 = $result->e2;
                    $away->e2 = $result->e1;
                    $away->v1 = $result->v2;
                    $away->v2 = $result->v1;
                    $away->decision = $result->decision;
                    $away->rtype = $result->rtype;
                    $away->id = $result->id;
                    $away->roundid = $result->roundid;
                    $away->new_match_id = $result->new_match_id;
                    $away->show_report = $result->show_report;

                    $teamRow->{$bucket}[$colId] = $home;
                    $teams[$colId]->{$bucket}[$rowId] = $away;
                }
            }
        }

        return $teams;
    }

    /**
     * Load match results for the active project, optional round and division.
     *
     * Requires the caller's project ID to match the project currently
     * resolved by Joomla's database selector. The optional division filter
     * selects only matches in that division or its direct descendants; the
     * earlier LEFT JOIN without a matching WHERE clause was not a filter.
     * All dynamic identifiers are bound, and database exceptions stay in
     * Joomla's private log rather than leaking SQL details to visitors.
     *
     * @param int $projectId Project requested by the frontend matrix view.
     * @param int|bool $unpublished Whether unpublished match rows are included.
     * @return array<int,object> Matching matrix rows, or empty on invalid input.
     */
    public function getMatrixResults($projectId, $unpublished = 0)
    {
        $projectId = (int) $projectId;

        if ($projectId <= 0 || $projectId !== $this->projectId) {
            return [];
        }

        $roundId = self::$roundid;
        $divisionId = self::$divisionid;
        $db = $this->getDatabase();
        $query = $db->createQuery();

        $query->select('DISTINCT(m.id),m.show_report,m.cancel,m.division_id AS division_id,m.cancel_reason,m.projectteam1_id,m.projectteam2_id');
        $query->select('m.team1_result as e1,m.team2_result as e2,m.match_result_type as rtype,m.alt_decision as decision,m.team1_result_decision AS v1');
        $query->select('m.team2_result_decision AS v2,m.new_match_id, m.old_match_id');
        $query->select('r.name AS roundname,r.id AS roundid,r.roundcode');
        $query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug');
        $query->from('#__sportsmanagement_match AS m');
        $query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
        $query->join('LEFT', '#__sportsmanagement_project_team AS tt1 ON m.projectteam1_id = tt1.id');
        $query->join('LEFT', '#__sportsmanagement_project_team AS tt2 ON m.projectteam2_id = tt2.id');
        $query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = tt1.team_id');
        $query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = tt2.team_id');
        $query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');

        if ($divisionId > 0) {
            $query->join(
                'LEFT',
                '#__sportsmanagement_division AS d1 ON m.division_id = d1.id'
                . ' AND (d1.id = :matrixDivisionId OR d1.parent_id = :matrixParentDivisionId)'
            )
                ->bind(':matrixDivisionId', $divisionId, ParameterType::INTEGER)
                ->bind(':matrixParentDivisionId', $divisionId, ParameterType::INTEGER)
                ->where('d1.id IS NOT NULL');
        }

        $query->where('r.project_id = :matrixProjectId')
            ->bind(':matrixProjectId', $projectId, ParameterType::INTEGER);

        if ($roundId > 0) {
            $query->where('m.round_id = :matrixRoundId')
                ->bind(':matrixRoundId', $roundId, ParameterType::INTEGER);
        }

        if ((int) $unpublished !== 1) {
            $query->where('m.published = 1');
        }

        $query->order('roundcode');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (Throwable $exception) {
            $this->reportDatabaseError($exception);

            return [];
        }
    }

    /**
     * Log a matrix database exception privately and show generic feedback.
     *
     * Database driver error text may contain table names, paths, raw SQL or
     * credentials. Keep that detail in Joomla's private error log and show
     * only a translated generic failure to public matrix visitors.
     *
     * @param Throwable $e Database operation failure.
     * @return void
     */
    private function reportDatabaseError(Throwable $e): void
    {
        Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');
        $this->siteApplication()->enqueueMessage(
            Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            'error'
        );
    }
}
