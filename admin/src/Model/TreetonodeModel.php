<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;

/** Native Joomla 5/6 administrator form model for one tournament-tree node. */
final class TreetonodeModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.treetonode',
            'treetonode',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'Treetonode', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    public function getNode(int $nodeId = 0): ?object
    {
        if ($nodeId <= 0) {
            $nodeId = Factory::getApplication()->getInput()->getInt('id');
        }

        if ($nodeId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto_node'))
            ->where($db->quoteName('id') . ' = ' . $nodeId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getNodeMatch(int $nodeId = 0): array
    {
        if ($nodeId <= 0) {
            $nodeId = Factory::getApplication()->getInput()->getInt('id');
        }

        if ($nodeId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('ttm.node_id') . ' = ' . $nodeId)
            ->order($db->quoteName('mc.id') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function setUnpublishNode(int $nodeId = 0): bool
    {
        if ($nodeId <= 0) {
            $nodeId = Factory::getApplication()->getInput()->post->getInt('id');
        }

        if ($nodeId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_treeto_node'))
            ->set($db->quoteName('published') . ' = 0')
            ->where($db->quoteName('id') . ' = ' . $nodeId);

        try {
            $db->setQuery($query)->execute();

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('project_type'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getProjectTeamsOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name'),
                $db->quoteName('t.middle_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('t.name') . ' ASC');
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
