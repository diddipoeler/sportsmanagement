<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      teampersons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 

/**
 * sportsmanagementControllerteampersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerteampersons extends JControllerAdmin
{
	
  /**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
        $this->app = JFactory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
$this->registerTask('unpublish', 'set_season_team_state');
$this->registerTask('publish', 'set_season_team_state');
$this->registerTask('trash', 'set_season_team_state');
$this->registerTask('archive', 'set_season_team_state');
	}



/**
 * sportsmanagementControllerteampersons::set_season_team_state()
 * 
 * @return void
 */
function set_season_team_state()
{
$post = JFactory::getApplication()->input->get( 'post' );
$ids = $this->input->get('cid', array(), 'array');
$tpids = $this->input->get('tpid', array(), 'array');
$values = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2);
$task = $this->getTask();
$value = ArrayHelper::getValue($values, $task, 0, 'int');    

//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask <br><pre>'.print_r($this->getTask(),true).'</pre>'),'Notice');   
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ids    <br><pre>'.print_r($ids,true).'</pre>'),'Notice');            
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tpids    <br><pre>'.print_r($tpids,true).'</pre>'),'Notice');   
$model = $this->getModel();
$model->set_state($ids,$tpids,$value);  

switch ($value)
{
case 0:
$ntext = 'COM_SPORTSMANAGEMENT_N_ITEMS_UNPUBLISHED';
break;
case 1:
$ntext = 'COM_SPORTSMANAGEMENT_N_ITEMS_PUBLISHED';		
break;
case 2:
$ntext = 'COM_SPORTSMANAGEMENT_N_ITEMS_ARCHIVED';		
break;
case -2:
$ntext = 'COM_SPORTSMANAGEMENT_N_ITEMS_TRASHED';		
break;		
}		

$this->setMessage(JText::plural($ntext, count($ids)));	
	
$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&persontype='.$post['persontype'].'&project_team_id='.$post['project_team_id'].'&team_id='.$post['team_id'].'&pid='.$post['pid']  , false));    
}
	
  /**
	 * Method to update checked teamplayers
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
    function saveshort()
	{
	   $post = JFactory::getApplication()->input->get( 'post' );
	   $model = $this->getModel();
       $model->saveshort();
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&persontype='.$post['persontype'].'&project_team_id='.$post['project_team_id'].'&team_id='.$post['team_id'].'&pid='.$post['pid']  , false));
    } 
  
  /**
   * sportsmanagementControllerteampersons::remove()
   * 
   * @return void
   */
  function remove()
	{
	$app = JFactory::getApplication();
    $pks = JFactory::getApplication()->input->getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('teampersons');
    $model->remove($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=teampersons');    
        
   }
   
  /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'teamperson', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	


	
}
