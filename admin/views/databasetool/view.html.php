<?php
/** Native-compatible administrator database-tool view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewDatabaseTool extends sportsmanagementView
{
    public function init($tpl = null): void
    {
        $this->request_url = Uri::getInstance()->toString();
        $this->task = $this->app->getInput()->getCmd('task');
        $this->step = 0;
        $this->totals = 0;
        $this->work_table = '';
        $this->bar_value = 100;
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE');
        $this->icon = 'database';
        parent::addToolbar();
    }
}
