<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage transifex
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
JLoader::import('components.com_sportsmanagement.helpers.transifex', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementViewtransifex
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewtransifex extends sportsmanagementView
{


	/**
	 * sportsmanagementViewtransifex::init()
	 *
	 * @return void
	 */
	public function init()
	{

		// $lang = Factory::getLanguage();
		// $langtag = $lang->getTag();
		$langtag = ComponentHelper::getParams('com_languages')->get('site');
		$code = sportsmanagementHelperTransifex::getLangCode($langtag, false, true);

		if ($langtag == 'de-DE' || $langtag == 'en-GB')
		{
			 $this->app->enqueueMessage(Text::_('Admin Verzeichnis ' . $langtag . ' ist vorhanden!'), 'Notice');
			 $this->language = array();
		}
		else
		{
			 $result = sportsmanagementHelperTransifex::getData('');
			 $json_decode = json_decode($result['data']);
			 $transifexlanguages = sportsmanagementHelperTransifex::getData('languages');
			 $json_decode = json_decode($transifexlanguages['data']);
			 $transifexresources = sportsmanagementHelperTransifex::getData('resources');
			 $this->transifexresources = json_decode($transifexresources['data']);

			 $translatefiles = array();

			foreach ($this->transifexresources as $key => $value)
			{
				$resourceData = sportsmanagementHelperTransifex::getData('resource/' . $value->slug . '/stats');
				$temparray = json_decode($resourceData['data']);
				$object = new stdClass;
				$object->file = $value->name;
				$object->slug = $value->slug;
				$object->languagetag = $langtag;
				$object->language = $code;
				$object->images = '';

				// $object->completed = $temparray[$code]->completed;
				foreach ((array) json_decode($resourceData['data']) as $langCode => $lang)
				{
					if ($langCode == $code)
					{
						$object->completed = $lang->completed;
						$object->untranslated_words = $lang->untranslated_words;
						$object->untranslated_entities = $lang->untranslated_entities;
						$object->translated_words = $lang->translated_words;
						$object->translated_entities = $lang->translated_entities;
					}
				}

				$translatefiles[] = $object;
			}

			 $this->language = sportsmanagementHelperTransifex::updatelanguage($translatefiles, $langtag);
		}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->jinput->set('hidemainmenu', true);
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TRANSIFEX');
		$this->icon = 'transifex';
		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement');
		parent::addToolbar();
	}




}
