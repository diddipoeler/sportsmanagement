<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      resultsranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage resultsranking
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

jimport( 'joomla.application.component.model' );

/**
 * sportsmanagementModelResultsranking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelResultsranking extends JModelLegacy
{
	
    static $divisionid= 0;
    static $roundid= 0;
    static $projectid= 0;
    static $cfg_which_database = 0;
    
    /**
	 * sportsmanagementModelResultsranking::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
	   $app = Factory::getApplication();
       // JInput object
       $jinput = $app->input;
       
		parent::__construct( );
		self::$divisionid = (int) $jinput->get('division', 0, '');
		self::$roundid = (int) $jinput->get('r', 0, '');
		self::$projectid = (int) $jinput->get('p', 0, '');
		self::$cfg_which_database = $jinput->get('cfg_which_database', 0 ,'');
    sportsmanagementModelProject::$projectid = self::$projectid;
    sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
	}
  
  }
  
?>  
