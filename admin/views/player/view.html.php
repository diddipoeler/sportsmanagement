<?php
/** Joomla 5/6 administrator player view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewplayer extends sportsmanagementView
{
    public function init()
    {
        $this->item->name = $this->item->lastname . ' - ' . $this->item->firstname;

        foreach (['sports_type_id', 'position_id', 'agegroup_id', 'person_art', 'person_id1', 'person_id2'] as $field) {
            $this->form->setValue($field, 'request', $this->item->{$field} ?? null);
        }

        if ((float) ($this->item->latitude ?? 0) === 255.0) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'error');
            $this->map = false;
        } else {
            $this->map = true;
        }

        foreach (['birthday', 'deathday', 'injury_date_start', 'injury_date_end', 'susp_date_start', 'susp_date_end', 'away_date_start', 'away_date_end'] as $field) {
            if (($this->item->{$field} ?? '') === '0000-00-00') {
                $this->item->{$field} = '';
                $this->form->setValue($field, null, '');
            }
        }

        $this->extended = sportsmanagementHelper::getExtended($this->item->extended ?? '', 'player');
        $this->extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser ?? '', 'player');
        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend', 0, 'player');
        $this->lists = [];

        if ($this->checkextrafields) {
            $this->lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields((int) $this->item->id, 'backend', 0, 'player');
        }

        $personAge = sportsmanagementHelper::getAge(
            $this->form->getValue('birthday'),
            $this->form->getValue('deathday')
        );
        $personRange = $this->model->getAgeGroupID($personAge);

        if ($personRange) {
            $this->form->setValue('agegroup_id', null, $personRange);
        }

        $this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/sm_functions.js');
        $this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/editgeocode.js');

        $language = $this->app->getLanguage();
        $language->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, $language->getDefault(), true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, null, true);
    }

    protected function addToolBar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        $this->title = $this->item->id
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_EDIT')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_NEW');
        $this->icon = 'person';
        parent::addToolbar();
    }
}
