<?php
/** SportsManagement administrator tournament-tree node edit view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewTreetonode extends sportsmanagementView
{
    public function init()
    {
        if (in_array($this->getLayout(), ['edit', 'edit_3', 'edit_4'], true)) {
            $this->displayForm();
        }
    }

    private function displayForm(): void
    {
        $input = $this->app->getInput();
        $projectId = $input->getInt('pid') ?: (int) $this->app->getUserState($this->option . '.pid', 0);
        $treeId = $input->getInt('tid') ?: (int) $this->app->getUserState($this->option . '.tid', 0);
        $nodeId = !empty($this->item->id) ? (int) $this->item->id : $input->getInt('id');
        $project = $this->model->getProject($projectId);

        if (!$project) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $teamOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM')),
        ];
        $projectTeams = $this->model->getProjectTeamsOptions($projectId);

        if ($projectTeams) {
            $teamOptions = array_merge($teamOptions, $projectTeams);
        }

        $this->project_id = $projectId;
        $this->tree_id = $treeId;
        $this->projectws = $project;
        $this->lists = ['team' => $teamOptions];
        $this->node = $this->item;
        $this->match = $this->model->getNodeMatch($nodeId);
    }
}
