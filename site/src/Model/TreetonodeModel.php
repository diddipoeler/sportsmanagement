<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 model for the tree-to-node frontend view. */
final class TreetonodeModel extends SportsManagementProjectModel
{
    public int $projectid = 0;
    public int $treetoid = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        $this->projectid = $this->getProjectId();
        $this->treetoid = max(0, $input->getInt('tnid', 0));
    }

    public function getTreetonode(): array|false
    {
        if ($this->projectid <= 0) {
            $app = Factory::getApplication();
            $app->enqueueMessage(
                Text::sprintf(
                    'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_ERROR_1',
                    $app->getInput()->getInt('s', 0)
                ),
                'error'
            );

            return false;
        }

        if ($this->treetoid <= 0) {
            $this->treetoid = (int) $this->getTreeNodeID($this->projectid);
        }

        if ($this->treetoid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'ttn.*',
                $db->quoteName('ttn.id', 'ttnid'),
                $db->quoteName('ttm.match_id'),
                $db->quoteName('c.country', 'country'),
                $db->quoteName('c.logo_small', 'logo_small'),
                $db->quoteName('c.logo_middle', 'logo_middle'),
                $db->quoteName('c.logo_big', 'logo_big'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.middle_name', 'middle_name'),
                $db->quoteName('t.short_name', 'short_name'),
                $db->quoteName('t.id', 'tid'),
                $db->quoteName('ttn.title', 'title'),
                $db->quoteName('ttn.content', 'content'),
                $db->quoteName('tt.tree_i', 'tree_i'),
                $db->quoteName('tt.hide', 'hide'),
            ])
            ->from($db->quoteName('#__sportsmanagement_treeto_node', 'ttn'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('ttn.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_treeto', 'tt') . ' ON ' . $db->quoteName('tt.id') . ' = ' . $db->quoteName('ttn.treeto_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_treeto_match', 'ttm') . ' ON ' . $db->quoteName('ttm.node_id') . ' = ' . $db->quoteName('ttn.id'))
            ->where($db->quoteName('ttn.treeto_id') . ' = ' . $this->treetoid)
            ->order($db->quoteName('ttn.row'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getTreeNodeID(int $projectid = 0): int
    {
        if ($projectid <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('project_id') . ' = ' . $projectid);

        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    public function getNodeMatches(int $ttnid = 0): array
    {
        if ($ttnid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mc.id', 'value'),
                "CONCAT(t1.name, '_vs_', t2.name, ' [round:', r.roundcode, ']') AS text",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'mc'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('mc.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('mc.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('pt1.team_id') . ' = ' . $db->quoteName('st1.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('pt2.team_id') . ' = ' . $db->quoteName('st2.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('mc.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_treeto_match', 'ttm') . ' ON ' . $db->quoteName('mc.id') . ' = ' . $db->quoteName('ttm.match_id'))
            ->where($db->quoteName('ttm.node_id') . ' = ' . $ttnid)
            ->order($db->quoteName('mc.id'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getRoundName(): array
    {
        if ($this->projectid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectid)
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->order($db->quoteName('r.roundcode'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
