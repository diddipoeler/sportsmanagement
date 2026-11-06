<?php
/**
 * Joomla 5/6 administrator tournament-tree node form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetonodeTable;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 administrator form model for one tournament-tree node. */
final class TreetonodeModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native tournament-tree node table.
     * @param string $type Requested table type.
     * @param string $prefix Legacy table namespace.
     * @param array $config Optional table configuration.
     * @return mixed Native node table or parent table.
     */
    public function getTable($type = 'Treetonode', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Treetonode') === 0) {
            return new TreetonodeTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Load a tournament-tree node by ID or from the current request.
     * @param int $nodeId Tree node identifier, or zero for request ID.
     * @return object|null Selected node or null when unavailable.
     */
    public function getNode(int $nodeId = 0): ?object
    {
        if ($nodeId <= 0) {
            $nodeId = $this->administratorApplication()->getInput()->getInt('id');
        }

        if ($nodeId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto_node'))
            ->where($db->quoteName('id') . ' = :nodeId')
            ->bind(':nodeId', $nodeId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * Load matches linked to a tournament-tree node.
     * @param int $nodeId Node ID or zero to read the current request.
     * @return array<int,object> Node matches and joined team information.
     */
    public function getNodeMatch(int $nodeId = 0): array
    {
        if ($nodeId <= 0) {
            $nodeId = $this->administratorApplication()->getInput()->getInt('id');
        }

        if ($nodeId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mc.id', 'mid'),
                $db->quoteName('mc.match_number'),
                $db->quoteName('t1.name', 'projectteam1'),
                $db->quoteName('mc.team1_result', 'projectteam1result'),
                $db->quoteName('mc.team2_result', 'projectteam2result'),
                $db->quoteName('t2.name', 'projectteam2'),
                $db->quoteName('mc.round_id', 'rid'),
                $db->quoteName('mc.published'),
                $db->quoteName('ttm.node_id'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('mc.checked_out'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'mc'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('mc.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('mc.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('mc.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_treeto_match', 'ttm') . ' ON ' . $db->quoteName('ttm.match_id') . ' = ' . $db->quoteName('mc.id'))
            ->where($db->quoteName('ttm.node_id') . ' = :nodeId')
            ->order($db->quoteName('mc.id') . ' ASC')
            ->bind(':nodeId', $nodeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Unpublish a selected tournament-tree node.
     * Uses an integer-bound node identifier from the explicit argument
     * or the administrator POST data.
     * @param int $nodeId Node ID or zero for posted ID.
     * @return bool Whether the database update succeeded.
     */
    public function setUnpublishNode(int $nodeId = 0): bool
    {
        if ($nodeId <= 0) {
            $nodeId = $this->administratorApplication()->getInput()->post->getInt('id');
        }

        if ($nodeId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->update($db->quoteName('#__sportsmanagement_treeto_node'))
            ->set($db->quoteName('published') . ' = 0')
            ->where($db->quoteName('id') . ' = :nodeId')
            ->bind(':nodeId', $nodeId, ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Find tournament project metadata by integer project ID.
     * @param int $projectId Project identifier.
     * @return object|null Project record or null when unavailable.
     */
    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('project_type'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * List project teams for tournament node selection controls.
     * @param int $projectId Tournament project identifier.
     * @return array<int,object> Available project team options.
     */
    public function getProjectTeamsOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name'),
                $db->quoteName('t.middle_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->order($db->quoteName('t.name') . ' ASC')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
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
}
