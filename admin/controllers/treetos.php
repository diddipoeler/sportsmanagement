<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treetos.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Session\Session; 
use Joomla\Utilities\ArrayHelper; 
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementControllertreetos
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllertreetos extends JSMControllerAdmin
{

/**
 * sportsmanagementControllertreetos::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
	{
		parent::__construct($config);
        // Reference global application object
        $this->jsmapp = Factory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
	}
      
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'treeto', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
    
public function genNode()
	{
		$id = $this->jsmjinput->get->get('id');
		$this->setRedirect('index.php?option=com_sportsmanagement&view=treeto&layout=gennode&id=' . $id);
	}
        
/**
 * sportsmanagementControllertreetos::save()
 * 
 * @return void
 */
public function save()
	{
		// Check for token
		Session::checkToken() or jexit(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
		
		//$app = Factory::getApplication();
		//$jinput = $app->input;
		$cid = $this->jsmjinput->get('cid',array(),'array');
		ArrayHelper::toInteger($cid);
		
		$post = $this->jsmjinput->post->getArray();
		$data['project_id'] = $post['project_id'];

//        $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' post <pre>'.print_r($post, true).'</pre><br>',''); 
//        $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' data <pre>'.print_r($data, true).'</pre><br>',''); 
		
        $model = $this->getModel('treeto');
        $row = $model->getTable();
        
		if($row->save($data))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_SAVED') . $model->getError();
		}
        
        
		// Check the table in so it can be edited.... we are done with it anyway
		// $model->checkin();
		
		$task = $this->getTask();
		
		if($task == 'save')
		{
			$link = 'index.php?option=com_sportsmanagement&view=treetos';
		}
		else
		{
			$link = 'index.php?option=com_sportsmanagement&task=treeto.edit&id=' . $post['id'];
		}
		$this->setRedirect($link,$msg);
	}    
    
    
    
    
    
}