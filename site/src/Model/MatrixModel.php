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
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use stdClass;
use Throwable;

/** Joomla 5/6 model for matrix views. */
final class MatrixModel extends SportsManagementProjectModel
{
    public static int $divisionid = 0;
    public static int $roundid = 0;
    public static int $projectid = 0;
    public static int $cfg_which_database = 0;

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

    public function getDivision(?int $divisionId = null): ?object
    {
        return parent::getDivision($divisionId ?? self::$divisionid);
    }

    public function getRound(): ?object
    {
        if (self::$roundid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . self::$roundid);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getDivisions(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'd.*',
                "CONCAT_WS(':', d.id, d.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = ' . $this->projectId)
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

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

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

    public function getMatrixResults($projectId, $unpublished = 0)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

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

        if (self::$divisionid > 0) {
            $divisionId = (int) self::$divisionid;
            $query->join(
                'LEFT',
                '#__sportsmanagement_division AS d1 ON m.division_id = d1.id'
                . ' AND (d1.id = ' . $divisionId . ' OR d1.parent_id = ' . $divisionId . ')'
            );
        }

        $query->where('r.project_id = ' . (int) $projectId);

        if (self::$roundid > 0) {
            $query->where('m.round_id = ' . (int) self::$roundid);
        }

        if ((int) $unpublished !== 1) {
            $query->where('m.published = 1');
        }

        $query->order('roundcode');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
