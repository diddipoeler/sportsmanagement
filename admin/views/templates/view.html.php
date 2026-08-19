<?php
/** SportsManagement administrator project templates list view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewTemplates extends sportsmanagementView
{
    public function init()
    {
        $this->state = $this->get('State');
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        $this->project_id = $this->model->getProjectId() ?: (int) $this->project_id;
        $project = $this->model->getProject();
        $this->lists = [];

        if (!$project) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');
            $project = (object) [
                'id' => $this->project_id,
                'master_template' => 0,
            ];
        }

        $templates = $this->get('Items') ?: [];

        if (!empty($project->master_template)) {
            $allMasterTemplates = $this->model->getMasterTemplatesList(1);
            $masterTemplates = $this->model->getMasterTemplatesList(0);

            foreach ($masterTemplates as $template) {
                $template->text = Text::_((string) $template->text);
            }

            $importList = [
                HTMLHelper::_(
                    'select.option',
                    0,
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_SELECT_FROM_MASTER')
                ),
            ];
            $importList = array_merge($importList, $masterTemplates);
            $this->lists['mastertemplates'] = HTMLHelper::_(
                'select.genericlist',
                $importList,
                'templateid',
                'class="inputbox" onchange="Joomla.submitform(\'template.masterimport\', this.form);"'
            );
            $this->master = $this->model->getMasterName();
            $templates = array_merge($templates, $allMasterTemplates);
        }

        $this->templates = $templates;
        $this->projectws = $project;
        $this->pagination = $this->get('Pagination');
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TITLE');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . (int) $this->project_id
        );
        ToolbarHelper::editList('template.edit');

        if (!empty($this->projectws->master_template)) {
            ToolbarHelper::deleteList('', 'template.remove', 'JTOOLBAR_DELETE');
        } else {
            ToolbarHelper::custom(
                'template.reset',
                'unblock',
                'unblock',
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET')
            );
            ToolbarHelper::custom(
                'template.update',
                'wand',
                'wand',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_UPDATE')
            );
        }

        ToolbarHelper::checkin('templates.checkin');
        parent::addToolbar();
    }
}
