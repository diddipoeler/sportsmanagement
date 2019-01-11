<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      projectreferee.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * sportsmanagementControllerprojectreferee
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerprojectreferee extends JSMControllerForm
{

/**
	 * Method to remove a projectreferee
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function remove()
	{
	$app =& Factory::getApplication();
    $pks = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('projectreferee');
    $model->delete($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=projectreferees');    
        
   }   

}
