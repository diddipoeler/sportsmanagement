<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetonode;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodeModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for one tournament-tree node. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public $node;
    public array $match = [];
    public $projectws;
    public int $project_id = 0;
    public int $tree_id = 0;
    public array $lists = [];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $app->getInput()->set('hidemainmenu', true);

        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'edit';
        $this->setLayout($layout === 'edit' ? 'edit' : $layout);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $model = $this->getModel();

        if (!$model instanceof TreetonodeModel || !$this->form || !$this->item) {
            throw new \RuntimeException('Tournament tree node could not be loaded.', 500);
        }

        $this->project_id = $input->getInt('pid') ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $this->tree_id = $input->getInt('tid') ?: (int) $app->getUserState('com_sportsmanagement.tid', 0);

        if ($this->project_id > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->project_id);
        }
        if ($this->tree_id > 0) {
            $app->setUserState('com_sportsmanagement.tid', $this->tree_id);
        }

        $this->projectws = $model->getProject($this->project_id);

        if (!$this->projectws) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
        }

        $teamOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM')),
        ];
        $projectTeams = $model->getProjectTeamsOptions($this->project_id);
        if ($projectTeams) {
            $teamOptions = array_merge($teamOptions, $projectTeams);
        }

        $this->lists['team'] = $teamOptions;
        $this->node = $this->item;
        $this->match = $model->getNodeMatch((int) ($this->item->id ?? 0));

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TITLE'), 'tree-2');
        ToolbarHelper::apply('treetonode.apply');
        ToolbarHelper::save('treetonode.save');
        ToolbarHelper::cancel('treetonode.cancel', 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
