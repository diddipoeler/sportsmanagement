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
		$this->coefficientyear = $this->jsmjinput->getString('coefficientyear', '');
		
	}
    
        
    /**
     * sportsmanagementModeluefawertung::getcoefficientyears()
     * 
     * @return void
     */
    function getcoefficientyears()
    {
        
        
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