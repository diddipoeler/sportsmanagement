<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      person.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;

/**
 * sportsmanagementControllerperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerperson extends JSMControllerForm
{

    /**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
    }    


///**
// * sportsmanagementControllerperson::cancel()
// * 
// * @return void
// */
//function cancel()
//	{
//      if ( $this->view_list == 'people' )
//      {
//      $this->view_list == 'persons' ; 
//      }
//       $this->setRedirect(Route::_('index.php?option='.$this->jsmoption.'&view='.$this->view_list, false));
//    } 

}
