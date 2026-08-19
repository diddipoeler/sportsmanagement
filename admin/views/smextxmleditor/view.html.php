<?php
/**
 * SportsManagement administrator extended XML/PHP editor view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewsmextxmleditor extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $this->file_name = $input->getString('file_name', '');
        $this->form = $this->get('Form');
        $this->source = $this->get('Source');
    }

    protected function addToolbar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        parent::addToolbar();
        ToolbarHelper::apply('smextxmleditor.apply');
        ToolbarHelper::save('smextxmleditor.save');
        ToolbarHelper::cancel('smextxmleditor.cancel', 'JTOOLBAR_CANCEL');
        $this->title = $this->file_name;
        $this->icon = 'xml-edit';
    }
}
