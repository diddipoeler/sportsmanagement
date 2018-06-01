<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextlmoimports
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewjlextlmoimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewjlextlmoimports extends sportsmanagementView
{

	/**
	 * sportsmanagementViewjlextlmoimports::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$lang = JFactory::getLanguage();

		$config = JComponentHelper::getParams('com_media');
		$post = $this->jinput->post->getArray(array());
		$files = $this->jinput->getArray(array('files'));

		//$this->request_url	= $uri->toString();
		$this->config	= $config;
		$teile = explode("-",$lang->getTag());
		$country = JSMCountries::convertIso2to3($teile[1]);
		$this->country	= $country;
		$countries = JSMCountries::getCountryOptions();
		$lists['countries'] = JHtml::_('select.genericlist', $countries, 'country', 'class="inputbox" size="1"', 'value', 'text', $country);
		$this->countries	= $lists['countries'];
   
	}
    
    /**
     * sportsmanagementViewjlextlmoimports::addToolbar()
     * 
     * @return void
     */
    protected function addToolbar() 
    {
        JToolbarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=extensions');
        parent::addToolbar();
	}
    



  
}
?>
