<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewplayer
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewplayer extends sportsmanagementView
{

	/**
	 * sportsmanagementViewplayer::init()
	 *
	 * @return void
	 */
	public function init()
	{

		/**
		 * name für den titel setzen
		 */
		$this->item->name = $this->item->lastname . ' - ' . $this->item->firstname;

		$this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
		$this->form->setValue('position_id', 'request', $this->item->position_id);
		$this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);

		$this->form->setValue('person_art', 'request', $this->item->person_art);
		$this->form->setValue('person_id1', 'request', $this->item->person_id1);
		$this->form->setValue('person_id2', 'request', $this->item->person_id2);

		if ($this->item->latitude == 255) {
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'Error');
			$this->map = false;
		} else {
			$this->map = true;
		}

		$isNew = $this->item->id == 0;

		$fields = array('birthday', 'deathday', 'injury_date_start', 'injury_date_end', 'susp_date_start', 'susp_date_end', 'away_date_start', 'away_date_end');
		foreach ($fields as $field) {
			if ($this->item->$field == '0000-00-00') {
				$this->item->$field = '';
				$this->form->setValue($field, null, '');
			}
		}

		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'player');
		$this->extended = $extended;
		$extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'player');
		$this->extendeduser = $extendeduser;
		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend', 0, Factory::getApplication()->input->get('view'));
		$lists = array();

		if ($this->checkextrafields) {
			$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id, 'backend', 0, Factory::getApplication()->input->get('view'));
		}

		$this->lists = $lists;
		unset($lists);

		$person_age = sportsmanagementHelper::getAge($this->form->getValue('birthday'), $this->form->getValue('deathday'));
		$person_range = $this->model->getAgeGroupID($person_age);

		if ($person_range) {
			$this->form->setValue('agegroup_id', null, $person_range);
		}

		$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/sm_functions.js');

		/**
		 * Load the language files for the contact integration
		 */
		$jlang = Factory::getLanguage();
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, null, true);

		//$this->document->addScript('https://maps.googleapis.com/maps/api/js?libraries=places&language=de');
		//$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/geocomplete.js');
		//$this->document->addScript(Uri::base() . 'components/' . $this->option . '/views/person/tmpl/edit.js');
		$this->document->addScript(Uri::base() . 'components/' . $this->option . '/assets/js/editgeocode.js');

	}

	/**
	 * sportsmanagementViewplayer::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_NEW');
		$this->icon = 'person';
		parent::addToolbar();

	}

}