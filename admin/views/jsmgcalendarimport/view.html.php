<?php
/** SportsManagement administrator Google calendar import view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjsmgcalendarImport extends sportsmanagementView
{
    public function init($tpl = null)
    {
        $this->setLayout('login');
    }

    protected function addToolbar()
    {
        ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');
        parent::addToolbar();
    }
}
