<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



// Include dependancy of the main controllerform class
jimport('joomla.application.component.controllerform');


class sportsmanagementControllerEditClub extends JControllerForm
{



public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
        {
                return parent::getModel($name, $prefix, array('ignore_request' => false));
        }
        
	function load()
	{
		$cid = JRequest::getInt( 'cid', 0 );

		$club = & JTable::getInstance( 'Club', 'sportsmanagementTable' );
		$club->load( $cid );
		$club->checkout( $user->id );

		$this->display();
	}


    function display()
	{
		/*
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
        */
	}

	function save()
	{
		$mainframe = JFactory::getApplication();
    // Check for request forgeries
		JRequest::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
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
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_SAVED');
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
            $type='message';
		}
		else
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_ERROR_SAVE').$model->getError();
            $type='error';
		}
		
        // Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		/*
        if ($this->getTask()=='save')
		{
			$link='index.php?option=com_joomleague&view=editclub';
		}
		else
		{
			$link='index.php?option=com_joomleague&view=editclub&cid='.$post['id'];
		}
        */
        
        //$link = JoomleagueHelperRoute::getClubInfoRoute( $project_id, $post['id'] );
		//$this->setRedirect($link,$msg);
        
        $this->setRedirect('index.php?option=com_sportsmanagement&close='.JRequest::getString('close', 0).'&tmpl=component&view=editclub&cid='.$post['id'],$msg,$type);
        
	}

	

	

	
	
	
	
}
?>