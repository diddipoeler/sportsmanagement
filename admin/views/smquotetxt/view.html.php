<?php
/** SportsManagement administrator quote text editor view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

class sportsmanagementViewsmquotetxt extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $this->file_name = $input->getString('file_name');
        $this->form = $this->get('Form');
        $this->source = $this->get('Source');
        $this->option = 'com_sportsmanagement';
    }

    protected function addToolbar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        $this->title = $this->file_name !== ''
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_EDIT')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_ADD_NEW');
        $this->icon = 'quote';

        parent::addToolbar();
    }
}
