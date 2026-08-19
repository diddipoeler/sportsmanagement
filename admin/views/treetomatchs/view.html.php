<?php
/** SportsManagement administrator tournament-tree match assignments view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewTreetomatchs extends sportsmanagementView
{
    public function init()
    {
        $layout = $this->getLayout();

        if (in_array($layout, ['editlist', 'editlist_3', 'editlist_4'], true)) {
            $this->displayEditList();

            return;
        }

        if (in_array($layout, ['default', 'default_3', 'default_4'], true)) {
            $this->displayDefaultList();
        }
    }

    private function displayEditList(): void
    {
        $input = $this->app->getInput();
        $projectId = $input->getInt('pid');
        $nodeId = $input->getInt('nid');
        $treeId = $input->getInt('tid');
        $project = $this->model->getProject($projectId);
        $node = $this->model->getNode($nodeId);

        if (!$project || !$node) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $assigned = $this->model->getNodeMatches($nodeId);
        $available = $this->model->getMatches($nodeId, $treeId, $projectId);
        $assignedIds = array_fill_keys(array_map(
            static fn ($row): int => (int) $row->value,
            $assigned
        ), true);

        $assignedOptions = array_map(
            static fn ($row) => HTMLHelper::_('select.option', (int) $row->value, (string) $row->text),
            $assigned
        );
        $availableOptions = [];

        foreach ($available as $row) {
            if (!isset($assignedIds[(int) $row->value])) {
                $availableOptions[] = HTMLHelper::_('select.option', (int) $row->value, (string) $row->text);
            }
        }

        $this->lists = [
            'node_matches' => HTMLHelper::_(
                'select.genericlist',
                $assignedOptions,
                'node_matcheslist[]',
                'class="form-select" multiple size="15"',
                'value',
                'text'
            ),
            'matches' => HTMLHelper::_(
                'select.genericlist',
                $availableOptions,
                'matcheslist[]',
                'class="form-select" multiple size="15"',
                'value',
                'text'
            ),
        ];
        $this->project_id = $projectId;
        $this->tree_id = $treeId;
        $this->node_id = $nodeId;
        $this->projectws = $project;
        $this->nodews = $node;
        $this->treetomatchs = $this->items;
        $this->addToolbarEditList();
        $this->setLayout('editlist');
    }

    private function displayDefaultList(): void
    {
        $input = $this->app->getInput();
        $projectId = $input->getInt('pid');
        $nodeId = $input->getInt('nid');
        $treeId = $input->getInt('tid');
        $project = $this->model->getProject($projectId);
        $node = $this->model->getNode($nodeId);

        if (!$project || !$node) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $this->project_id = $projectId;
        $this->tree_id = $treeId;
        $this->node_id = $nodeId;
        $this->match = $this->items;
        $this->projectws = $project;
        $this->nodews = $node;
        $this->addToolbarDefault();
        $this->setLayout('default');
    }

    private function addToolbarEditList(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN'));
        ToolbarHelper::save('treetomatch.save_matcheslist');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=treetonodes&layout=default'
            . '&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id
        );
    }

    private function addToolbarDefault(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_TITLE'));
        ToolbarHelper::custom(
            'treetomatch.editlist',
            'edit',
            'edit',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_BUTTON_ASSIGN'),
            false
        );
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=treetonodes&layout=default'
            . '&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id
        );
    }
}
