<?php
/** SportsManagement administrator Google calendars list view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjsmgcalendars extends sportsmanagementView
{
    public function init()
    {
        $this->model->check_google_api();
    }

    protected function addToolbar()
    {
        ToolbarHelper::addNew('jsmgcalendar.add', 'JTOOLBAR_NEW');
        ToolbarHelper::custom(
            'jsmgcalendarimport.import',
            'upload.png',
            'upload.png',
            'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_BUTTON_IMPORT',
            false
        );
        $this->icon = 'google-calendar-48-icon.png';
        parent::addToolbar();
    }
}
