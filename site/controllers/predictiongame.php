<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictiongame.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage prediction
 */

defined('_JEXEC') or die( 'Restricted access' );
use Joomla\CMS\MVC\Controller\BaseController;

require_once JLG_PATH_EXTENSION_PREDICTIONGAME .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'route.php' ;

/**
 * sportsmanagementControllerPredictiongame
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictiongame extends BaseController
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