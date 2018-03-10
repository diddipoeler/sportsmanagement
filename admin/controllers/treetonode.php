<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treetonode.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */
 
defined('_JEXEC') or die;

//jimport('joomla.application.component.controller');
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * sportsmanagementControllerTreetonode
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerTreetonode extends JControllerForm
{



	/**
	 * sportsmanagementControllerTreetonode::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
		//$jinput->set('layout','form');

		parent::__construct($config);
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'Notice');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($this->jsmjinput->get('pid'),true).'</pre>'),'Notice');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tid<br><pre>'.print_r($this->jsmjinput->get('tid'),true).'</pre>'),'Notice');
        
        //$this->jsmjinput->set('pid',$this->jsmjinput->get('pid'));
        
        $this->jsmapp->setUserState($this->jsmoption.'.pid',$this->jsmjinput->get('pid') );
        $this->jsmapp->setUserState($this->jsmoption.'.tid',$this->jsmjinput->get('tid') );
        
        //parent::__construct($config);
        //$this->getTask()
	}






/**
 * sportsmanagementControllerTreetonode::cancel()
 * 
 * @param mixed $key
 * @return void
 */
function cancel($key = NULL)
{
$pid = $this->jsmapp->getUserState( $this->jsmoption . '.pid' );
$tid = $this->jsmapp->getUserState( $this->jsmoption . '.tid' );    
$msg = '';
$link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid='.$tid.'&pid='.$pid;    
$this->setRedirect($link,$msg);    
}

//public function save($key = NULL, $urlVar = NULL)
/**
 * sportsmanagementControllerTreetonode::save()
 * 
 * @param mixed $key
 * @param mixed $urlVar
 * @return void
 */
function save($key = NULL, $urlVar = NULL)
	{
	   //$data = JFactory::getApplication()->input->getVar('jform', array(), 'post', 'array');
       $data = $this->jsmjinput->post->getArray();
       
       //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key<br><pre>'.print_r($key,true).'</pre>'),'Notice');
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' urlVar<br><pre>'.print_r($urlVar,true).'</pre>'),'Notice');
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'Notice');
       
   			$pid = $this->jsmapp->getUserState( $this->jsmoption . '.pid' );
            $tid = $this->jsmapp->getUserState( $this->jsmoption . '.tid' );
       
       //$task = $this->getTask();
       $model = $this->getModel('treetonode');
       $data['id'] = $this->jsmjinput->get('id');
       $result = $model->save($data);
       
       switch ($this->getTask())
       {
       case 'apply':
       if($result)
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
        //http://localhost/joomla/administrator/index.php?option=com_sportsmanagement&task=treetonode.edit&id=243&tid=12&pid=5889
       $link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.edit&id='.$this->jsmjinput->get('id').'&tid='.$tid.'&pid='.$pid;
       break;
       case 'save':
		if($result)
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
        $link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid='.$tid.'&pid='.$pid;
       break;  
        
       }
            
               //$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');

			//$link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid='.$tid.'&pid='.$pid;

		$this->setRedirect($link,$msg);
 
    
    }




	/**
	 * sportsmanagementControllerTreetonode::removenode()
	 * 
	 * @return void
	 */
	public function removenode()
	{
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		$post = $this->jsmjinput->post->getArray();
		//$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);
        
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');

		$model = $this->getModel('treetonodes');
		if($model->setRemoveNode($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_REMOVENODE');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_REMOVENODE');
		}
        
		$link = 'index.php?option=com_sportsmanagement&view=treetos';
		$this->setRedirect($link,$msg);
	}

	/**
	 * sportsmanagementControllerTreetonode::saveshortleaf()
	 * 
	 * @return void
	 */
	public function saveshortleaf()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));

		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		//$cid = $jinput->get('cid',array(),'array');
        $cid = $this->jsmjinput->get('cid',array(),'array');
		JArrayHelper::toInteger($cid);
		$post = $this->jsmjinput->post->getArray(array());
		//$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);
        $post['treeto_id'] = (int) $this->jsmjinput->get('tid');
		$model = $this->getModel('treetonodes');

		if($model->storeshortleaf($cid,$post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
		$link = 'index.php?option=com_sportsmanagement&view=treetonodes&tid='.$this->jsmjinput->get('tid').'&pid='.$this->jsmjinput->get('pid');
		$this->setRedirect($link,$msg);
	}


	/**
	 * sportsmanagementControllerTreetonode::savefinishleaf()
	 * 
	 * @return void
	 */
	public function savefinishleaf()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));

		$cid = $this->jsmjinput->get('cid',array(),'array');
		JArrayHelper::toInteger($cid);
		$post = $this->jsmjinput->post->getArray(array());
		//$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);
        $post['treeto_id'] = (int) $this->jsmjinput->get('tid');

		$model = $this->getModel('treetonodes');
		if($model->storefinishleaf($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_ERROR_SAVED');
		}
		$link = 'index.php?option=com_sportsmanagement&view=treetonodes&tid='.$this->jsmjinput->get('tid').'&pid='.$this->jsmjinput->get('pid');
		$this->setRedirect($link,$msg);
	}

	
	/**
	 * sportsmanagementControllerTreetonode::saveshort()
	 * 
	 * @return void
	 */
	public function saveshort()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));

	//	$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
		$cid = $this->jsmjinput->get('cid',array(),'array');
		JArrayHelper::toInteger($cid);
		$post = $this->jsmjinput->post->getArray(array());

		$model = $this->getModel('treetonodes');
		if($model->storeshort($cid,$post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
		$link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode&tid='.$this->jsmjinput->get('tid').'&pid='.$this->jsmjinput->get('pid');
		$this->setRedirect($link,$msg);
	}
    
}
