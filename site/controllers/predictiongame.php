<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictiongame.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage prediction
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

require_once JLG_PATH_EXTENSION_PREDICTIONGAME . DS . 'helpers' . DS . 'route.php' ;

/**
 * sportsmanagementControllerPredictiongame
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictiongame extends JControllerLegacy
{
	/**
	 * sportsmanagementControllerPredictiongame::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
}
?>