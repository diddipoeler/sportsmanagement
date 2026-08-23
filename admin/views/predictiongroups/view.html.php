<?php
/**
 * SportsManagement administrator prediction-groups list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiongroupTable;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewpredictiongroups extends sportsmanagementView
{
    public function init()
    {
        $this->table = new PredictiongroupTable($this->model->getDatabase());

        if (!$this->items) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GROUPS'), 'error');
        }
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_TITLE');
        $this->icon = 'predgroups';

        ToolbarHelper::addNew('predictiongroup.add');
        ToolbarHelper::editList('predictiongroup.edit');
        ToolbarHelper::custom('predictiongroup.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('predictiongroup.export', Text::_('JTOOLBAR_EXPORT'));
        ToolbarHelper::deleteList('', 'predictiongroups.delete', 'JTOOLBAR_DELETE');
        ToolbarHelper::checkin('predictiongroups.checkin');
        parent::addToolbar();
    }
}
