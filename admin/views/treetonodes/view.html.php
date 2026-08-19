<?php
/** SportsManagement administrator tournament-tree nodes view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewTreetonodes extends sportsmanagementView
{
    public function init()
    {
        if (in_array($this->getLayout(), ['default', 'default_3', 'default_4'], true)) {
            $this->displayDefault();
        }
    }

    private function displayDefault(): void
    {
        $input = $this->app->getInput();
        $projectId = $input->getInt('pid') ?: (int) $this->app->getUserState($this->option . '.pid', 0);
        $treeId = $input->getInt('tid') ?: (int) $this->app->getUserState($this->option . '.tid', 0);
        $project = $this->model->getProject($projectId);
        $tree = $this->model->getTreeToData($treeId);

        if (!$project || !$tree) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $teamOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEAMS_LEGEND')),
        ];
        $projectTeams = $this->model->getProjectTeamsOptions($projectId);

        if ($projectTeams) {
            $teamOptions = array_merge($teamOptions, $projectTeams);
        }

        $this->node = $this->items;
        $this->project_id = $projectId;
        $this->tree_id = $treeId;
        $this->lists = ['team' => $teamOptions];
        $this->style = 'style="background-color:#dddddd;border:0;font-weight:normal;font-size:8pt;width:150px;font-family:verdana;text-align:center;"';
        $this->path = 'media/com_sportsmanagement/treebracket/onwhite/';
        $this->projectws = $project;
        $this->treetows = $tree;
        $this->matches = $this->model->getteamsprorunde($projectId, $tree);

        foreach ($this->node as $value) {
            $bracketNode = $this->matches[(int) $value->node] ?? null;

            if (!$bracketNode) {
                continue;
            }

            $value->team_id = (int) $bracketNode->team_id;
            $value->team_name = (string) $bracketNode->team_name;
            $value->title = (string) $bracketNode->team_name;
            $value->content = (string) $bracketNode->team_name;
            $value->match_id = (int) $bracketNode->match_id;
            $value->roundcode = (int) $bracketNode->roundcode;
        }

        if (!$this->model->savenode($this->node)) {
            $this->app->enqueueMessage($this->model->getError(), 'error');
        }
    }

    protected function addToolBar()
    {
        if (empty($this->treetows)) {
            return;
        }

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TITLE');

        switch ((int) $this->treetows->leafed) {
            case 1:
                ToolbarHelper::apply(
                    'treetonode.saveshort',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_APPLY')
                );
                ToolbarHelper::custom(
                    'treetonode.removenode',
                    'delete',
                    'delete',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE_ALL'),
                    false
                );
                break;

            case 2:
                ToolbarHelper::apply(
                    'treetonode.saveallleaf',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TEST_SHOW')
                );
                ToolbarHelper::custom(
                    'treetonode.removenode',
                    'delete',
                    'delete',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE'),
                    false
                );
                break;

            case 3:
                ToolbarHelper::apply(
                    'treetonode.savefinishleaf',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_LEAF')
                );
                ToolbarHelper::custom(
                    'treetonode.removenode',
                    'delete',
                    'delete',
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE'),
                    false
                );
                break;
        }

        parent::addToolbar();
    }
}
