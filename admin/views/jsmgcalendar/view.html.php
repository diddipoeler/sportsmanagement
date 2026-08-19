<?php
/** SportsManagement administrator Google calendar edit view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;

class sportsmanagementViewjsmgcalendar extends sportsmanagementView
{
    public function init()
    {
        $this->description = '';

        if ((int) $this->item->id < 1) {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $this->form->setValue('username', null, $params->get('google_mail_account', ''));
            $this->form->setValue('password', null, $params->get('google_mail_password', ''));
        }

        $this->formparams = sportsmanagementHelper::getExtended(
            $this->item->params,
            'jsmgcalendar'
        );
    }

    protected function addToolbar()
    {
        parent::addToolbar();
    }
}
