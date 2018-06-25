<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      round.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * sportsmanagementControllerround
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerround extends JSMControllerForm
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

	/**
	 * sportsmanagementControllerround::startpopulate()
	 * 
	 * @return void
	 */
	function startpopulate()
	{
	$msgType = 'message';
	$msg = '';
	$model = $this->getModel('rounds');	
	$post = JFactory::getApplication()->input->post->getArray(array());	
	$project_id = $post['project_id'];
	$scheduling = $post['scheduling'];
	$time       = $post['time'];
	$interval   = $post['interval'];
	$start      = $post['start'];
	$roundname  = $post['roundname'];
	$teamsorder = $post['teamsorder'];
	
	if ( !$teamsorder )
	{
	$msg = Jtext::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB');
	$msgType = 'error';
	}
	else
	{
	$res = $model->populate($project_id, $scheduling, $time, $interval, $start, $roundname, $teamsorder);	
	}
//JFactory::getApplication()->enqueueMessage(JText::_('sportsmanagementViewMatches _season_id<br><pre>'.print_r($post,true).'</pre>'),'');		
		
	$this->setRedirect('index.php?option=com_sportsmanagement&view=rounds', $msg, $msgType);	
	}
 

}
