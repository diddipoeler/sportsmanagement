<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');


class sportsmanagementControllerTreetonode extends JControllerLegacy
{

	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('edit','display');
		$this->registerTask('apply','save');
	}

	public function display($cachable = false, $urlparams = false)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();

	 	$model=$this->getModel('treetonodes');
		$viewType=$document->getType();
		$view=$this->getView('treetonodes',$viewType);
		$view->setModel($model,true);	// true is for the default model;

		$projectws=$this->getModel('project');
		$projectws->setId($mainframe->getUserState($option.'project',0));
		$view->setModel($projectws);
		if ( $tid = JRequest::getVar( 'tid', null, '', 'array' ) )
		{
			$mainframe->setUserState( $option . 'treeto_id', $tid[0] );
		}
		$treetows = $this->getModel( 'treeto' );
		$treetows->setId( $mainframe->getUserState( $option.'treeto_id') );
		$view->setModel( $treetows );
		
		switch($this->getTask())
		{
			case 'edit'	:
			{
				$model=$this->getModel('treetonode');
				$viewType=$document->getType();
				$view=$this->getView('treetonode',$viewType);
				$view->setModel($model,true);	// true is for the default model;

				$view->setModel($projectws);

				JRequest::setVar('hidemainmenu',0);
				JRequest::setVar('layout','form');
				JRequest::setVar('view','treetonode');
				JRequest::setVar('edit',true);

				$model=$this->getModel('treetonode');
				$model->checkout();
			} break;
		}
		parent::display();
	}

	public function removenode()
	{
		
		$mainframe	= JFactory::getApplication();
		$post	= JRequest::get( 'post' );
		$post['treeto_id']=$mainframe->getUserState($option.'treeto_id',0);
		$model		= $this->getModel( 'treetonodes' );
		if ( $model->setRemoveNode() )
		{
			$msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_REMOVENODE' );
		}
		else
		{
			$msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_REMOVENODE' );
		}
		$link = 'index.php?option=com_joomleague&view=treetos&task=treeto.display';
		$this->setRedirect( $link, $msg );
	}

	public function unpublishnode()
	{
		$mainframe	= JFactory::getApplication();
		$post	= JRequest::get( 'post' );
		$model		= $this->getModel( 'treetonode' );
		if ( $model->setUnpublishNode() )
		{
			$msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_UNPUBLISHNODE' );
		}
		else
		{
			$msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_UNPUBLISHNODE' );
		}
		$link = 'index.php?option=com_joomleague&view=treetonodestreetonode.display';
		$this->setRedirect( $link, $msg );
	}

	// save the checked nodes inside the trees
	public function saveshortleaf()
	{
		JSession::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$post=JRequest::get('post');
		$post['treeto_id']=$mainframe->getUserState($option.'treeto_id',0);
		$cid=JRequest::getVar('cid',array(),'post','array');
		JArrayHelper::toInteger($cid);
		$model=$this->getModel('treetonodes');

		if ($model->storeshortleaf($cid,$post))
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED').$model->getError();
		}
		$link='index.php?option=com_joomleague&view=treetonodes&task=treetonode.display';
		$this->setRedirect($link,$msg);
	}
	public function savefinishleaf()
	{
		JSession::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$post	= JRequest::get( 'post' );
		$post['treeto_id']=$mainframe->getUserState($option.'treeto_id',0);
		$model		= $this->getModel( 'treetonodes' );

		if ( $model->storefinishleaf() )
		{
			$msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_SAVED' );
		}
		else
		{
			$msg = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_ERROR_SAVED' );
		}
		$link = 'index.php?option=com_joomleague&view=treetonodes&task=treetonode.display';
		$this->setRedirect( $link, $msg );
	}
	// save the checked nodes inside the trees
	public function saveshort()
	{
		JSession::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$post=JRequest::get('post');
		$cid=JRequest::getVar('cid',array(),'post','array');
		JArrayHelper::toInteger($cid);
		$model=$this->getModel('treetonodes');
		if ($model->storeshort($cid,$post))
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED').$model->getError();
		}
		$link='index.php?option=com_joomleague&view=treetonodes&task=treetonode.display';
		$this->setRedirect($link,$msg);
	}

	public function save()
	{
		JSession::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$post=JRequest::get('post');
		$model=$this->getModel('treetonode');

		if ($model->store($post))
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED').$model->getError();
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		if ($this->getTask()=='save')
		{
			$link='index.php?option=com_joomleague&view=treetonodes&task=treetonode.display';
		}
		else
		{
			$link='index.php?option=com_joomleague&view=treetonodes&task=treetonode.edit&cid[]='.$post['id'];
		}
		$this->setRedirect($link,$msg);
	}

	//	assign (empty)match to node	from editmatches view
	public function assignmatch()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$post=JRequest::get('post');
		$post['project_id']=$mainframe->getUserState($option.'project',0);
		$post['node_id']=$mainframe->getUserState($option.'node_id',0);
		$model=$this->getModel('treetonode');
		if ($model->store($post))
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ADD_MATCH');
		}
		else
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_ADD_MATCH').$model->getError();
		}
		$link='index.php?option=com_joomleague&view=matches&task=match.display';
		$this->setRedirect($link,$msg);
	}

}
?>