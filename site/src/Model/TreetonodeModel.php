<?php
/**
 * Native Joomla 5/6 model for the tree-to-node frontend view.
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
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 model for the tree-to-node frontend view. */
final class TreetonodeModel extends SportsManagementProjectModel
{
    public int $projectid = 0;
    public int $treetoid = 0;

    /**
     * Initialise tournament-tree node data through Joomla site MVC.
     *
     * Preserve the project model's database and factory configuration,
     * then read the selected project and tree identifiers from the
     * validated site application request.
     *
     * @param array $config Joomla model configuration options.
     * @param MVCFactoryInterface|null $factory Optional native Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->projectid = $this->getProjectId();
        $this->treetoid = max(0, $input->getInt('tnid', 0));
    }

    /**
     * Fetch the selected tournament tree's nodes and related team information.
     *
     * Reject missing project context, resolve the project's tree when no
     * node tree was selected, and join node, match, team, club and tree data
     * using a bound tree identifier. Preserve legacy false for invalid
     * projects and an empty list when the project has no tournament tree.
     *
     * @return array<int,object>|false Node rows, or false for a missing project.
     */
    public function getTreetonode(): array|false
    {
        if ($this->projectid <= 0) {
            $app = $this->siteApplication();
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
        $treeToId = $this->treetoid;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ttn') . '.*',
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
            ->where($db->quoteName('ttn.treeto_id') . ' = :treeToId')
            ->bind(':treeToId', $treeToId, ParameterType::INTEGER)
            ->order($db->quoteName('ttn.row'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Find the first tournament-tree identifier owned by a project.
     *
     * Bind the validated positive project ID to Joomla's query builder
     * and avoid running the lookup when the project is unselected.
     *
     * @param int $projectid Positive SportsManagement project ID.
     * @return int Tournament-tree ID or zero if none exists.
     */
    public function getTreeNodeID(int $projectid = 0): int
    {
        if ($projectid <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('project_id') . ' = :treeProjectId')
            ->bind(':treeProjectId', $projectid, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /**
     * Load match selection options associated with one tournament-tree node.
     *
     * Join project teams, team names and round information to produce
     * match IDs and descriptive labels. The node ID is bound as an
     * integer rather than interpolated into the query.
     *
     * @param int $ttnid Selected tournament-tree node identifier.
     * @return array<int,object> Matching Joomla select-option rows.
     */
    public function getNodeMatches(int $ttnid = 0): array
    {
        if ($ttnid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('mc.id', 'value'),
                'CONCAT(' . $db->quoteName('t1.name') . ", '_vs_', " . $db->quoteName('t2.name') . ", ' [round:', " . $db->quoteName('r.roundcode') . ", ']') AS " . $db->quoteName('text'),
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
            ->where($db->quoteName('ttm.node_id') . ' = :treeNodeId')
            ->bind(':treeNodeId', $ttnid, ParameterType::INTEGER)
            ->order($db->quoteName('mc.id'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * List tournament rounds for the currently selected project.
     *
     * Filter rounds by the bound project identifier and tournament flag,
     * then order results by their round code. Unselected projects produce
     * an empty list without a database query.
     *
     * @return array<int,object> Tournament-round records.
     */
    public function getRoundName(): array
    {
        if ($this->projectid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $projectId = $this->projectid;
        $query = $db->createQuery()
            ->select($db->quoteName('r') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundProjectId')
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->bind(':roundProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode'));

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
