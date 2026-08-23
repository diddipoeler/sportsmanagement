<?php
/**
 * SportsManagement administrator project positions view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewprojectpositions extends sportsmanagementView
{
    public function init(): void
    {
        $input = $this->app->getInput();
        $model = $this->getModel();

        if (in_array($this->getLayout(), ['editlist', 'editlist_3', 'editlist_4'], true)) {
            $this->displayEditlist($model, $input->getInt('pid'));

            return;
        }

        $this->state = $this->get('State');
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        $items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->project_id = $input->getInt('pid');

        if ($this->project_id <= 0) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $input->set('pid', $this->project_id);
        $model->updateprojectpositions($items, $this->project_id);

        $this->project = $this->loadProject($this->project_id);

        if (!$this->project) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $this->notes[] = Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_LEGEND',
            '<i>' . $this->project->name . '</i>'
        );
        $this->positiontool = $items;
        $this->request_url = Uri::getInstance()->toString();
    }

    private function displayEditlist(object $model, int $projectId): void
    {
        $items = $this->get('Items') ?: [];
        $project = $this->loadProject($projectId);

        if (!$project) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $assignedPositionIds = [];
        $projectPositions = [];

        foreach ($items as $item) {
            $positionId = (int) ($item->id ?? 0);

            if ($positionId <= 0) {
                continue;
            }

            $assignedPositionIds[$positionId] = true;
            $projectPositions[] = HTMLHelper::_('select.option', $positionId, Text::_($item->name));
        }

        $lists = [];
        $lists['project_positions'] = $projectPositions
            ? HTMLHelper::_(
                'select.genericlist',
                $projectPositions,
                'project_positionslist[]',
                'style="width:250px; height:250px;" class="inputbox" multiple="true" size="' . max(15, count($items)) . '"',
                'value',
                'text'
            )
            : '<select name="project_positionslist[]" id="project_positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';

        $availablePositions = [];
        $subPositions = $model->getSubPositions((int) $project->sports_type_id);

        if ($subPositions) {
            foreach ($subPositions as $position) {
                $positionId = (int) ($position->value ?? 0);

                if ($positionId <= 0 || isset($assignedPositionIds[$positionId])) {
                    continue;
                }

                $position->text = Text::_($position->text);
                $availablePositions[] = $position;
            }
        } else {
            Log::add(
                '<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_ASSIGN_POSITIONS_FIRST') . '<br /><br />',
                Log::WARNING,
                'jsmerror'
            );
        }

        $lists['positions'] = $availablePositions
            ? HTMLHelper::_(
                'select.genericlist',
                $availablePositions,
                'positionslist[]',
                'style="width:250px; height:250px;" class="inputbox" multiple="true" size="' . min(15, count($availablePositions)) . '"',
                'value',
                'text'
            )
            : '<select name="positionslist[]" id="positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';

        $this->project_id = $projectId;
        $this->project = $project;
        $this->lists = $lists;
        $this->request_url = Uri::getInstance()->toString();
        $this->document->addScript(Uri::base() . 'components/com_sportsmanagement/assets/js/sm_functions.js');
        $this->setLayout('editlist');
    }

    private function loadProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $projectModel = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Project', 'Administrator', ['ignore_request' => true]);

        if ($projectModel === null) {
            throw new \RuntimeException('SportsManagement project model not found.', 500);
        }

        $project = $projectModel->getItem($projectId);

        return is_object($project) && (int) ($project->id ?? 0) > 0 ? $project : null;
    }

    protected function addToolbar_Editlist()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_TITLE');
        ToolbarHelper::save('projectposition.save_positionslist');
        ToolbarHelper::cancel('projectposition.cancel', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOSE'));
        parent::addToolbar();
    }

    protected function addToolbar()
    {
        if ($this->getLayout() === 'editlist') {
            $this->addToolbar_Editlist();

            return;
        }

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TITLE');

        if ($this->project_id > 0) {
            ToolbarHelper::back(
                'JPREV',
                'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . (int) $this->project_id
            );
        }

        sportsmanagementHelper::ToolbarButton(
            'editlist',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_BUTTON_UN_ASSIGN')
        );
        parent::addToolbar();
    }
}
