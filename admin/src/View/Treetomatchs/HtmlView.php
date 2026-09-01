<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetomatchs;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchsModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for tournament-tree match assignments. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public int $project_id = 0;
    public int $tree_id = 0;
    public int $node_id = 0;
    public $projectws;
    public $nodews;
    public array $match = [];
    public array $treetomatchs = [];
    public array $lists = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof TreetomatchsModel) {
            throw new \RuntimeException('TreetomatchsModel could not be loaded.', 500);
        }

        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout(in_array($layout, ['default', 'editlist'], true) ? $layout : 'default');

        $this->state = $model->getState();
        $this->project_id = (int) $this->state->get('context.project_id', 0);
        $this->tree_id = (int) $this->state->get('context.tree_id', 0);
        $this->node_id = (int) $this->state->get('context.node_id', 0);
        $this->projectws = $model->getProject($this->project_id);
        $this->nodews = $model->getNode($this->node_id);

        if (!$this->projectws || !$this->nodews) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
        }

        if ($this->getLayout() === 'editlist') {
            $this->prepareAssignment($model);
            $this->addAssignmentToolbar();
        } else {
            $this->items = $model->getItems() ?: [];
            $this->pagination = $model->getPagination();
            $this->match = $this->items;
            $this->addDefaultToolbar();
        }

        parent::display($tpl);
    }

    private function prepareAssignment(TreetomatchsModel $model): void
    {
        $assigned = $model->getNodeMatches($this->node_id);
        $available = $model->getMatches($this->node_id, $this->tree_id, $this->project_id);
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

        $this->lists['node_matches'] = HTMLHelper::_(
            'select.genericList',
            $assignedOptions,
            'node_matcheslist[]',
            'id="node_matcheslist" class="form-select" multiple size="15"',
            'value',
            'text'
        );
        $this->lists['matches'] = HTMLHelper::_(
            'select.genericList',
            $availableOptions,
            'matcheslist[]',
            'id="matcheslist" class="form-select" multiple size="15"',
            'value',
            'text'
        );
        $this->treetomatchs = $assigned;
    }

    private function addAssignmentToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN'), 'link');
        ToolbarHelper::save('treetomatch.save_matcheslist');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=treetonodes&layout=default'
            . '&tid=' . $this->tree_id . '&pid=' . $this->project_id
        );
    }

    private function addDefaultToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_TITLE'), 'list');
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
            . '&tid=' . $this->tree_id . '&pid=' . $this->project_id
        );
    }
}
