<?php

defined('_JEXEC') or die;

//jimport('joomla.application.component.controller');
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

class sportsmanagementControllerTreetonode extends JControllerForm
{



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
	 * Function that allows child controller access to model data after the data
	 * has been saved.
	 *
	 * @param JModelLegacy $model	The data model object.
	 * @param array $validData		The validated data.
	 *
	 * @return void
	 */
	protected function postSaveHook(JModelLegacy $model,$validData = array())
	{
		return;
	}



function cancel($key = NULL)
{
$pid = $this->jsmapp->getUserState( $this->jsmoption . '.pid' );
$tid = $this->jsmapp->getUserState( $this->jsmoption . '.tid' );    
$msg = '';
$link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid='.$tid.'&pid='.$pid;    
$this->setRedirect($link,$msg);    
}

//public function save($key = NULL, $urlVar = NULL)
function save($key = NULL, $urlVar = NULL)
	{
	   //$data = JRequest::getVar('jform', array(), 'post', 'array');
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
/*
    public function edit($key = NULL, $urlVar = NULL)
    {
        
        
        
        
    }
*/

//	/**
//	 *
//	 */
//	public function __constructOBS()
//	{
//		parent::__construct();
//
//		// Register Extra tasks
//		$this->registerTask('edit','display');
//		$this->registerTask('apply','save');
//	}

//	/**
//	 *
//	 */
//	public function displayOBS($cachable = false,$urlparams = false)
//	{
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$document = JFactory::getDocument();
//		$model = $this->getModel('treetonodes');
//		$viewType = $document->getType();
//		$view = $this->getView('treetonodes',$viewType);
//		$view->setModel($model,true); // true is for the default model;
//
//		$projectws = $this->getModel('project');
//		$projectws->setId($app->getUserState($option . 'project',0));
//		$view->setModel($projectws);
//
//		$tid = $jinput->get('tid',array(),'array');
//
//		if($tid)
//		{
//			// set Treeto_id
//			JArrayHelper::toInteger($tid);
//			$app->setUserState($option . 'treeto_id',$tid[0]);
//		}
//		$treetows = $this->getModel('treeto');
//		$treetows->setId($app->getUserState($option . 'treeto_id'));
//		$view->setModel($treetows);
//
//		$task = $this->getTask();
//
//		switch($task)
//		{
//			case 'edit':
//				{
//					$model = $this->getModel('treetonode');
//					$viewType = $document->getType();
//					$view = $this->getView('treetonode',$viewType);
//					$view->setModel($model,true); // true is for the default
//					                               // model;
//					$view->setModel($projectws);
//
//					$jinput->set('hidemainmenu',false);
//					$jinput->set('layout','form');
//					$jinput->set('view','treetonode');
//					$jinput->set('edit',true);
//
//					$model = $this->getModel('treetonode');
//					$model->checkout();
//				}
//				break;
//		}
//		parent::display();
//	}


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
    
//	/**
//	 *
//	 */
//	public function removenodeOBS()
//	{
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$post = $jinput->post->getArray();
//		$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);
//
//		$model = $this->getModel('treetonodes');
//		if($model->setRemoveNode())
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_REMOVENODE');
//		}
//		else
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_REMOVENODE');
//		}
//		$link = 'index.php?option=com_sportsmanagement&view=treetos';
//		$this->setRedirect($link,$msg);
//	}

//	/**
//	 *
//	 */
//	public function unpublishnodeOBS()
//	{
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$post = $jinput->post->getArray();
//		$model = $this->getModel('treetonode');
//		if($model->setUnpublishNode())
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_UNPUBLISHNODE');
//		}
//		else
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_UNPUBLISHNODE');
//		}
//		$link = 'index.php?option=com_sportsmanagement&view=treetonodes';
//		$this->setRedirect($link,$msg);
//	}


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
	 * save the checked nodes inside the trees
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

//	/**
//	 *
//	 */
//	public function saveOBS()
//	{
//		// Check for token
//		JSession::checkToken() or jexit(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
//
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$post = $jinput->post->getArray();
//
//		$model = $this->getModel('treetonode');
//		if($model->store($post))
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
//		}
//		else
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
//		}
//		// Check the table in so it can be edited.... we are done with it anyway
//		$model->checkin();
//
//		$task = $this->getTask();
//
//		if($task == 'save')
//		{
//			$link = 'index.php?option=com_sportsmanagement&view=treetonodes';
//		}
//		else
//		{
//			$link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.edit&id=' . $post['id'];
//		}
//		$this->setRedirect($link,$msg);
//	}

//	/**
//	 * assign (empty)match to node from editmatches view
//	 */
//	public function assignmatchOBS()
//	{
//		$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$post = $jinput->post->getArray();
//		$post['project_id'] = $app->getUserState($option . 'project',0);
//		$post['node_id'] = $app->getUserState($option . 'node_id',0);
//
//		$model = $this->getModel('treetonode');
//		if($model->store($post))
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ADD_MATCH');
//		}
//		else
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_ADD_MATCH') . $model->getError();
//		}
//		$link = 'index.php?option=com_sportsmanagement&view=matches';
//		$this->setRedirect($link,$msg);
//	}
    
}
