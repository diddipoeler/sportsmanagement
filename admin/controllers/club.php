<?php
/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

/**
 * Joomleague Component Club Controller
 *
 * @package	JoomLeague
 * @since	0.1
 */
class JoomleagueControllerClub extends JoomleagueController
{
	protected $view_list = 'clubs';
	
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add','display');
		$this->registerTask('edit','display');
		$this->registerTask('apply','save');
	}

	function display()
	{
		switch($this->getTask())
		{
			case 'add'     :
				{
					JRequest::setVar('hidemainmenu',0);
					JRequest::setVar('layout','form');
					JRequest::setVar('view','club');
					JRequest::setVar('edit',false);

					// Checkout the club
					$model=$this->getModel('club');
					$model->checkout();
				} break;
			case 'edit'    :
				{
					JRequest::setVar('hidemainmenu',0);
					JRequest::setVar('layout','form');
					JRequest::setVar('view','club');
					JRequest::setVar('edit',true);

					// Checkout the club
					$model=$this->getModel('club');
					$model->checkout();
				} break;
		}
		parent::display();
	}

	function save()
	{
		$mainframe = JFactory::getApplication();
    // Check for request forgeries
		JRequest::checkToken() or die('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN');
		$msg='';
		$address_parts = array();
		$post=JRequest::get('post');
		
		//$mainframe->enqueueMessage(JText::_('post -> '.'<pre>'.print_r($post,true).'</pre>' ),'');
		
		$cid=JRequest::getVar('cid',array(0),'post','array');
		$post['id']=(int) $cid[0];
		$model=$this->getModel('club');
		
		if (!empty($post['address']))
		{
			$address_parts[] = $post['address'];
		}
		if (!empty($post['state']))
		{
			$address_parts[] = $post['state'];
		}
		if (!empty($post['location']))
		{
			if (!empty($post['zipcode']))
			{
				$address_parts[] = $post['zipcode']. ' ' .$post['location'];
			}
			else
			{
				$address_parts[] = $post['location'];
			}
		}
		if (!empty($post['country']))
		{
			$address_parts[] = Countries::getShortCountryName($post['country']);
		}
		$address = implode(', ', $address_parts);
		$coords = $model->resolveLocation($address);
		
		//$mainframe->enqueueMessage(JText::_('coords -> '.'<pre>'.print_r($coords,true).'</pre>' ),'');
		
		foreach( $coords as $key => $value )
		{
    $post['extended'][$key] = $value;
    }
		
		$post['latitude'] = $coords['latitude'];
		$post['longitude'] = $coords['longitude'];

    if (isset($post['merge_teams']))
		{
			if (count($post['merge_teams']) > 0)
			{
				$temp=implode(",",$post['merge_teams']);
			}
			else
			{
				$temp='';
			}
			$post['merge_teams']=$temp;
		}
		else
		{
			$post['merge_teams']='';
		}
		
    //$mainframe->enqueueMessage(JText::_('post -> '.'<pre>'.print_r($post,true).'</pre>' ),'');
		
		if ($model->store($post))
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_CLUB_CTRL_SAVED');
			$createTeam=JRequest::getVar('createTeam');
			if ($createTeam)
			{
				$team_name=JRequest::getVar('name');
				$team_short_name=strtoupper(substr(ereg_replace("[^a-zA-Z]","",$team_name),0,3));
				$teammodel=$this->getModel('team');
				$tpost['id']= "0";
				$tpost['name']= $team_name;
				$tpost['short_name']= $team_short_name ;
				$tpost['club_id']= $teammodel->getDbo()->insertid();
				$teammodel->store($tpost);
			}
		}
		else
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_CLUB_CTRL_ERROR_SAVE').$model->getError();
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		if ($this->getTask()=='save')
		{
			$link='index.php?option=com_joomleague&view=clubs&club.display';
		}
		else
		{
			$link='index.php?option=com_joomleague&task=club.edit&cid[]='.$post['id'];
		}
		$this->setRedirect($link,$msg);
	}

	function remove()
	{
		JRequest::checkToken() or die('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN');
		$cid=JRequest::getVar('cid',array(),'post','array');
		$msg='';
		JArrayHelper::toInteger($cid);
		if (count($cid) < 1){JError::raiseError(500,JText::_('COM_JOOMLEAGUE_ADMIN_CLUB_CTRL_SELECT_TO_DELETE'));}
		$model=$this->getModel('club');
		if(!$model->delete($cid))
		{
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
			return;
		}
		else
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_CLUB_CTRL_DELETED');
		}
		$this->setRedirect('index.php?option=com_joomleague&view=clubs&task=club.display',$msg);
	}

	function cancel()
	{
		// Checkin the club
		$model=$this->getModel('club');
		$model->checkin();
		$this->setRedirect('index.php?option=com_joomleague&view=clubs&task=club.display');
	}

	function import()
	{
		JRequest::setVar('view','import');
		JRequest::setVar('table','club');
		parent::display();
	}

	function export()
	{
		JRequest::checkToken() or die('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN');
		$post=JRequest::get('post');
		$cid=JRequest::getVar('cid',array(),'post','array');
		JArrayHelper::toInteger($cid);
		if (count($cid) < 1){JError::raiseError(500,JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT_TO_EXPORT'));}
		$model = $this->getModel("club");
		$model->export($cid, "club", "Club");
	}
	
	/**
	 * Proxy for getModel
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.6
	 */
	function getModel($name = 'Club', $prefix = 'JoomleagueModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
?>