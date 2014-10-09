<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');


class sportsmanagementControllerTreetomatch extends JControllerLegacy
{

	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add','display');
		$this->registerTask('edit','display');
		$this->registerTask('apply','save');
	}

	public function display($cachable = false, $urlparams = false)
	{
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

	 	$model=$this->getModel('treetomatchs');
		$viewType=$document->getType();
		$view=$this->getView('treetomatchs',$viewType);
		$view->setModel($model,true);	// true is for the default model;

		$projectws=$this->getModel('project');
		$projectws->setId($app->getUserState($option.'project',0));
		$view->setModel($projectws);
			if ( $nid = JRequest::getVar( 'nid', null, '', 'array' ) )
		{
			$app->setUserState( $option . 'node_id', $nid[0] );
		}
		if ( $tid = JRequest::getVar( 'tid', null, '', 'array' ) )
		{
			$app->setUserState( $option . 'treeto_id', $tid[0] );
		}
		$nodews = $this->getModel( 'treetonode' );
		$nodews->setId( $app->getUserState( $option.'node_id') );
		$view->setModel( $nodews );
		
		switch($this->getTask())
		{
			case 'edit'	:
			{
				$model=$this->getModel('treetomatch');
				$viewType=$document->getType();
				$view=$this->getView('treetomatch',$viewType);
				$view->setModel($model,true);	// true is for the default model;
				$view->setModel($projectws);
				
				JRequest::setVar('hidemainmenu',0);
				JRequest::setVar('layout','form');
				JRequest::setVar('view','treetomatch');
				JRequest::setVar('edit',true);

				$model=$this->getModel('treetomatch');
				$model->checkout();
			} break;

			case 'matchadd':
			{
				JRequest::setVar('matchadd',true);
			} break;

		}
		parent::display();
	}

	public function editlist()
	{
		$option = JRequest::getCmd('option');

		$app	= JFactory::getApplication();
		$document	= JFactory::getDocument();
		$model		= $this->getModel ('treetomatchs');
		$viewType	= $document->getType();
		$view		= $this->getView  ('treetomatchs', $viewType);
		$view->setModel($model, true);  // true is for the default model;

		$projectws = $this->getModel ('project');
		$projectws->setId($app->getUserState($option . 'project', 0));
		$view->setModel($projectws);
		
			if ( $nid = JRequest::getVar( 'nid', null, '', 'array' ) )
		{
			$app->setUserState( $option . 'node_id', $nid[0] );
		}
		if ( $tid = JRequest::getVar( 'tid', null, '', 'array' ) )
		{
			$app->setUserState( $option . 'treeto_id', $tid[0] );
		}
		$nodews = $this->getModel( 'treetonode' );
		$nodews->setId( $app->getUserState( $option.'node_id') );
		$view->setModel( $nodews );
		
		JRequest::setVar('hidemainmenu', 0);
		JRequest::setVar('layout', 'editlist' );
		JRequest::setVar('view', 'treetomatchs');
		JRequest::setVar('edit', true);

		// Checkout the project
		//	$model = $this->getModel('treetomatchs');

		parent::display();
	}

	public function save_matcheslist()
	{
		$post	= JRequest::get('post');
		$cid	= JRequest::getVar('cid', array(0), 'post', 'array');
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('treetomatchs');

		if ($model->store($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_CTRL_ERROR_SAVE') . $model->getError();
		}

		// Check the table in so it can be edited.... we are done with it anyway
		//$model->checkin();
		$link = 'index.php?option=com_joomleague&view=treetonodes&task=treetonode.display';
		$this->setRedirect($link, $msg);
	}

	public function publish()
	{
		$cid=JRequest::getVar('cid',array(),'post','array');
		JArrayHelper::toInteger($cid);
		if (count($cid) < 1){JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_PUBLISH'));}
		$model=$this->getModel('treetomatch');
		if (!$model->publish($cid,1)){echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";}
		$this->setRedirect('index.php?option=com_joomleague&task=treetomatch.display&view=treetomatchs');
	}

	public function unpublish()
	{
		$cid=JRequest::getVar('cid',array(),'post','array');
		JArrayHelper::toInteger($cid);
		if (count($cid) < 1){JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_UNPUBLISH'));}
		$model=$this->getModel('treetomatch');
		if (!$model->publish($cid,0)){echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";}
		$this->setRedirect('index.php?option=com_joomleague&task=treetomatch.display&view=treetomatchs');
	}
}
?>