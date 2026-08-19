<?php
/** SportsManagement administrator tournament trees list view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewTreetos extends sportsmanagementView
{
    public function init()
    {
        $this->project_id = $this->model->getProjectId()
            ?: (int) $this->app->getUserState("$this->option.pid", 0);
        $this->projectws = $this->model->getProject();
        $this->division = $this->app->getUserStateFromRequest(
            $this->option . 'tt_division',
            'division',
            '',
            'string'
        );

        $divisions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION')),
        ];
        $divisions = array_merge($divisions, $this->model->getDivisions());
        $this->lists = ['divisions' => $divisions];

        if (!$this->projectws) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');
            $this->projectws = (object) [
                'id' => $this->project_id,
                'name' => '',
                'project_type' => '',
            ];
        }
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE'), 'Tree');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . (int) $this->project_id
        );
        ToolbarHelper::apply('treeto.saveshort');
        ToolbarHelper::publishList('treetos.publish');
        ToolbarHelper::unpublishList('treetos.unpublish');
        ToolbarHelper::divider();
        ToolbarHelper::addNew('treetos.save');
        ToolbarHelper::deleteList(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_WARNING'),
            'treeto.remove'
        );
        ToolbarHelper::divider();
        parent::addToolbar();
    }
}
