<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextprofleagimport
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewjlextprofleagimport
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewjlextprofleagimport extends sportsmanagementView
{
	/**
	 * sportsmanagementViewjlextprofleagimport::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$lang = Factory::getLanguage();

		$config = ComponentHelper::getParams('com_media');
		$post = $this->jinput->post->getArray(array());
		$files = $this->jinput->get('files');

		$this->config    = $config;
		$teile = explode("-", $lang->getTag());
		$country = JSMCountries::convertIso2to3($teile[1]);
		$this->country    = $country;
		$countries = JSMCountries::getCountryOptions();
		$lists['countries'] = HTMLHelper::_('select.genericlist', $countries, 'country', 'class="inputbox" size="1"', 'value', 'text', $country);
		$this->countries    = $lists['countries'];

	}

	/**
	 * sportsmanagementViewjlextprofleagimport::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		// Set toolbar items for the page
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/' . $this->option . '/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$this->document->addCustomTag($stylelink);

			  // Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_TITLE_1'), 'profleage-cpanel');
		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
		ToolbarHelper::divider();
		parent::addToolbar();

	}





}
