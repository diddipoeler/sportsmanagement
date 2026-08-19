<?php
/** SportsManagement administrator tournament tree view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewTreeto extends sportsmanagementView
{
    public function init()
    {
        $layout = $this->getLayout();

        if (in_array($layout, ['gennode', 'gennode_3', 'gennode_4'], true)) {
            $this->displayGenerateNode();
        }
    }

    private function displayGenerateNode(): void
    {
        $this->form = $this->get('Form');
        $this->treeto = $this->get('Item');
        $input = $this->app->getInput();
        $this->project_id = $input->getInt('pid')
            ?: (int) $this->app->getUserState("$this->option.pid", 0);

        if ($this->project_id > 0) {
            $this->app->setUserState("$this->option.pid", $this->project_id);
        }

        $this->projectws = $this->model->getProject($this->project_id);
        $this->lists = [];

        if (!$this->projectws) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        $this->addToolbarGenerateNode();
        $this->setLayout('gennode');
    }

    private function addToolbarGenerateNode(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE_GENERATE'));
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=treetos&pid=' . (int) $this->project_id
        );
    }

    protected function addToolBar()
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE'));
        ToolbarHelper::save('treeto.save');
        ToolbarHelper::apply('treeto.apply');
    }
}
