<?php
/**
 * SportsManagement administrator divisions list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\DivisionTable;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewDivisions extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $projectId = (int) $this->app->getUserState("$this->option.pid", 0);

        if ($projectId <= 0) {
            $projectId = $input->getInt('pid');
        }

        $this->project_id = $projectId;
        $factory = $this->app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $projectModel = $factory->createModel('Project', 'Administrator');
        $this->projectws = $projectModel ? $projectModel->getProject($projectId) : null;
        $this->table = new DivisionTable($this->model->getDatabase());
        $this->lists = [];

        if (in_array($this->getLayout(), ['massadd', 'massadd_3', 'massadd_4'], true)) {
            $this->project = $this->projectws;
            $this->setLayout('massadd');
        }
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id
        );

        if (($this->user->username ?? '') === 'admin') {
            ToolbarHelper::publish('divisions.divisiontoproject', 'Division to Projekt', true);
        }

        ToolbarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('divisions.checkin');
        ToolbarHelper::apply('divisions.saveshort');
        ToolbarHelper::divider();
        sportsmanagementHelper::ToolbarButton(
            'massadd',
            'new',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_MASSADD_BUTTON')
        );
        ToolbarHelper::addNew('division.add');
        ToolbarHelper::editList('division.edit');
        parent::addToolbar();
    }
}
