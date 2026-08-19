<?php
/** SportsManagement administrator prediction member edit view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

class sportsmanagementViewpredictionmember extends sportsmanagementView
{
    public function init()
    {
        if (count($errors = $this->get('Errors'))) {
            Log::add(implode('<br />', $errors), Log::ERROR, 'com_sportsmanagement');

            return false;
        }

        return true;
    }

    protected function addToolBar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        $this->title = !empty($this->item->id)
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_EDIT')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_ADD_NEW');
        $this->icon = 'pmember';
        $this->item->name = '';

        parent::addToolbar();
    }
}
