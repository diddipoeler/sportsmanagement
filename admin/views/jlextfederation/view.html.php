<?php
/**
 * SportsManagement administrator federation edit view.
 */
defined('_JEXEC') or die('Restricted access');

class sportsmanagementViewJlextfederation extends sportsmanagementView
{
    public function init()
    {
        if ($this->item->id) {
            if ($this->item->founded === '0000-00-00') {
                $this->item->founded = '';
                $this->form->setValue('founded', null, '');
            }

            if ($this->item->dissolved === '0000-00-00') {
                $this->item->dissolved = '';
                $this->form->setValue('dissolved', null, '');
            }
        } else {
            $this->form->setValue('founded', null, '');
            $this->form->setValue('dissolved', null, '');
        }

        if (!$this->item->founded_year) {
            $this->item->founded_year = 'kein';
            $this->form->setValue('founded_year', '', 'kein');
        }
    }

    protected function addToolbar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        parent::addToolbar();
    }
}
