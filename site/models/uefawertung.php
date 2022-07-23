<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @file       uefawertung.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementModeluefawertung
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2022
 * @version $Id$
 * @access public
 */
class sportsmanagementModeluefawertung extends JSMModelLegacy
{
    
var $coefficientyear = '';

/**
 * sportsmanagementModeluefawertung::__construct()
 * 
 * @return void
 */
function __construct()
	{
		parent::__construct();
        $this->coefficientyear = $this->jsmjinput->post->get('coefficientyear', '');
		$this->coefficientyear = $this->jsmjinput->getString('coefficientyear', '');
		
	}
    
        
    /**
     * sportsmanagementModeluefawertung::getcoefficientyears()
     * 
     * @return void
     */
    function getcoefficientyears()
    {
        $result = array();
        $this->jsmquery->clear(); 
        $this->jsmquery->select('season AS id, season AS name');
		$this->jsmquery->from('#__sportsmanagement_uefawertung');
        $this->jsmquery->group('season');
		$this->jsmquery->order('season DESC');
        try
		{
        $this->jsmdb->setQuery($this->jsmquery );
        $result = $this->jsmdb->loadObjectList();
        }
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
		
		}
        
        return $result;
    }
    
    
    
    
    /**
     * sportsmanagementModeluefawertung::getcoefficientyearspoints()
     * 
     * @param string $coefficientyear
     * @return void
     */
    function getcoefficientyearspoints($coefficientyear = '')
    {
        
        
    }
    
}