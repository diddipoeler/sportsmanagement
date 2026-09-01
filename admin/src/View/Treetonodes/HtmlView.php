<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetonodes;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodesModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator bracket view for tournament-tree nodes. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public array $node = [];
    public int $project_id = 0;
    public int $tree_id = 0;
    public array $lists = [];
    public string $style = '';
    public string $path = '';
    public $projectws;
    public $treetows;
    public array $matches = [];
    public $state;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout === 'default' ? 'default' : $layout);

        $model = $this->getModel();

        if (!$model instanceof TreetonodesModel) {
            throw new \RuntimeException('TreetonodesModel could not be loaded.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->state = $model->getState();
        $this->project_id = $input->getInt('pid') ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $this->tree_id = $input->getInt('tid') ?: (int) $app->getUserState('com_sportsmanagement.tid', 0);

        if ($this->project_id > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->project_id);
        }
        if ($this->tree_id > 0) {
            $app->setUserState('com_sportsmanagement.tid', $this->tree_id);
        }

        $this->projectws = $model->getProject($this->project_id);
        $this->treetows = $model->getTreeToData($this->tree_id);

        if (!$this->projectws || !$this->treetows) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
        }

        $teamOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEAMS_LEGEND')),
        ];
        $projectTeams = $model->getProjectTeamsOptions($this->project_id);
        if ($projectTeams) {
            $teamOptions = array_merge($teamOptions, $projectTeams);
        }

        $this->node = $this->items;
        $this->lists['team'] = $teamOptions;
        $this->style = 'style="background-color:#dddddd;border:0;font-weight:normal;font-size:8pt;width:150px;font-family:verdana;text-align:center;"';
        $this->path = 'media/com_sportsmanagement/treebracket/onwhite/';
        $this->matches = $model->getteamsprorunde($this->project_id, $this->treetows);

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

        if (!$model->savenode($this->node)) {
            $app->enqueueMessage($model->getError(), 'error');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TITLE'), 'tree-2');

        switch ((int) $this->treetows->leafed) {
            case 1:
                ToolbarHelper::apply('treetonode.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_APPLY'));
                ToolbarHelper::custom('treetonode.removenode', 'delete', 'delete', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE_ALL'), false);
                break;
            case 2:
                ToolbarHelper::apply('treetonode.saveallleaf', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TEST_SHOW'));
                ToolbarHelper::custom('treetonode.removenode', 'delete', 'delete', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE'), false);
                break;
            case 3:
                ToolbarHelper::apply('treetonode.savefinishleaf', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_LEAF'));
                ToolbarHelper::custom('treetonode.removenode', 'delete', 'delete', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE'), false);
                break;
        }
    }
}
