<?php
/** SportsManagement administrator country edit view. */
defined('_JEXEC') or die('Restricted access');

class sportsmanagementViewJlextcountry extends sportsmanagementView
{
    public function init()
    {
    }

    protected function addToolBar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        parent::addToolbar();
    }
}
