<?php
/**
 * Native Joomla 5/6 administrator model for match staff and line-up reads.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchstaffTable;
use Joomla\CMS\Form\Form;

/**
 * Native Joomla 5/6 administrator form model for match staff.
 */
final class MatchstaffModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.matchstaff',
            'matchstaff',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    /**
     * Native staff-side replacement for legacy getMatchPersons(..., 'staff').
     *
     * @return array<int,object> keyed by season-team-person id
     */
    public function getMatchPersons(
        $projectTeamId = 0,
        $projectPositionId = 0,
        $matchId = 0,
        $projectId = 0
    ): array {
        $projectTeamId = (int) $projectTeamId;
        $projectPositionId = (int) $projectPositionId;
        $matchId = (int) $matchId;
        $projectId = $this->resolveProjectId((int) $projectId);

        if ($matchId <= 0 || $projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.team_staff_id'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.id', 'update_id'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('sp.id', 'value'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.knvbnr'),
                $db->quoteName('pos.name', 'positionname'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('mp.ordering', 'playerordering'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_staff', 'mp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp')
                . ' ON ' . $db->quoteName('mp.team_staff_id') . ' = ' . $db->quoteName('sp.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('sp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('sp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $projectId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('sp.person_id')
                . ' AND ' . $db->quoteName('ppp.persontype') . ' = ' . $db->quoteName('sp.persontype')
                . ' AND ' . $db->quoteName('ppp.project_id') . ' = ' . $projectId
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'pl')
                . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('sp.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id')
            )
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('pl.published') . ' = 1')
            ->order(
                $db->quoteName('mp.project_position_id') . ' ASC, '
                . $db->quoteName('mp.ordering') . ' ASC, '
                . $db->quoteName('pl.lastname') . ' ASC, '
                . $db->quoteName('pl.firstname') . ' ASC'
            );

        if ($projectTeamId > 0) {
            $query->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
        }

        if ($projectPositionId > 0) {
            $query->where($db->quoteName('mp.project_position_id') . ' = ' . $projectPositionId);
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList('team_staff_id') ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }

        foreach ($rows as $row) {
            $row->text = (string) ($row->firstname ?? '') . ':' . (string) ($row->lastname ?? '');
        }

        return $rows;
    }

    public function saveorder($pks = null, $order = null)
    {
        $pks = array_values((array) $pks);
        $order = array_values((array) $order);
        $row = $this->getTable();

        foreach ($pks as $index => $pk) {
            if (!array_key_exists($index, $order) || !$row->load((int) $pk)) {
                continue;
            }

            $ordering = (int) $order[$index];

            if ((int) $row->ordering === $ordering) {
                continue;
            }

            $row->ordering = $ordering;

            if (!$row->store()) {
                $this->setError((string) $row->getError());

                return false;
            }
        }

        return true;
    }

    public function getTable($type = 'matchstaff', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchstaff') === 0) {
            return new MatchstaffTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return $this->administratorApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }

    private function resolveProjectId(int $projectId): int
    {
        if ($projectId > 0) {
            return $projectId;
        }

        $app = $this->administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');

        return (int) $app->getUserState($option . '.pid', 0);
    }
}
