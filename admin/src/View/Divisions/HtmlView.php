<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Divisions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Table\DivisionTable;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use RuntimeException;

/**
 * Native Joomla 5/6 divisions list view.
 */
final class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;
    public $project;
    public $projectws;
    public $table;
    public array $lists = [];
    public int $projectId = 0;
    public int $project_id = 0;
    public int $close = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof DivisionsModel) {
            throw new RuntimeException('Divisions view requires DivisionsModel.', 500);
        }

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->projectId = $model->getProjectId();
        $this->project_id = $this->projectId;
        $this->project = $model->getProject();
        $this->projectws = $this->project;
        $this->table = new DivisionTable($model->getSportsManagementDatabase());
        $this->lists = [];

        $app = Factory::getApplication();
        $this->close = $app->getInput()->getInt('close', 0);

        if (in_array($this->getLayout(), ['massadd', 'massadd_3', 'massadd_4'], true)) {
            $this->setLayout('massadd');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE');

        if ($this->project && !empty($this->project->name)) {
            $title .= ': ' . $this->project->name;
        }

        ToolbarHelper::title($title, 'tree');

        if ($this->projectId > 0) {
            ToolbarHelper::back(
                'JPREV',
                'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->projectId
            );
        }

        $user = Factory::getApplication()->getIdentity();

        if (($user->username ?? '') === 'admin') {
            ToolbarHelper::publish('divisions.divisiontoproject', 'Division to Projekt', true);
        }

        ToolbarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('divisions.checkin');
        ToolbarHelper::apply('divisions.saveshort');
        ToolbarHelper::divider();

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $popupUrl = 'index.php?option=com_sportsmanagement'
            . '&view=divisions'
            . '&tmpl=component'
            . '&layout=massadd'
            . '&type=0'
            . '&issueview='
            . '&issuelayout='
            . '&pid=' . $this->projectId;

        Factory::getApplication()
            ->getDocument()
            ->getToolbar('toolbar')
            ->popupButton('massadd', Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_MASSADD_BUTTON'))
            ->url($popupUrl)
            ->icon('new')
            ->iframeWidth((int) $params->get('modal_popup_width', 0))
            ->iframeHeight((int) $params->get('modal_popup_height', 0));

        ToolbarHelper::addNew('division.add');
        ToolbarHelper::editList('division.edit');
    }
}
