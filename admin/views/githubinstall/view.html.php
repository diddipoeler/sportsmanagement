<?php
/** Joomla 5/6 GitHub update download view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewgithubinstall extends sportsmanagementView
{
    public function init()
    {
        $this->github_link = trim((string) ComponentHelper::getParams($this->option)->get('cfg_update_server_file', ''));
        $this->_success_text = [];
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL');
        parent::addToolbar();
    }
}
