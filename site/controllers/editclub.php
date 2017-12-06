<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



// Include dependancy of the main controllerform class
jimport('joomla.application.component.controllerform');


/**
 * sportsmanagementControllerEditClub
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerEditClub extends JControllerForm
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

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

/**
 * sportsmanagementControllerEditClub::getModel()
 * 
 * @param string $name
 * @param string $prefix
 * @param mixed $config
 * @return
 */
public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
        {
                return parent::getModel($name, $prefix, array('ignore_request' => false));
        }
        
	/**
	 * sportsmanagementControllerEditClub::load()
	 * 
	 * @return void
	 */
	function load()
	{
		$cid = JFactory::getApplication()->input->getInt( 'cid', 0 );

		$club = & JTable::getInstance( 'Club', 'sportsmanagementTable' );
		$club->load( $cid );
		$club->checkout( $user->id );

		$this->display();
	}


    /**
     * sportsmanagementControllerEditClub::display()
     * 
     * @return void
     */
    function display()
	{
		/*
        switch($this->getTask())
		{
			case 'add'     :
				{
					JFactory::getApplication()->input->setVar('hidemainmenu',0);
					JFactory::getApplication()->input->setVar('layout','form');
					JFactory::getApplication()->input->setVar('view','club');
					JFactory::getApplication()->input->setVar('edit',false);

					// Checkout the club
					$model=$this->getModel('club');
					$model->checkout();
				} break;
			case 'edit'    :
				{
					JFactory::getApplication()->input->setVar('hidemainmenu',0);
					JFactory::getApplication()->input->setVar('layout','form');
					JFactory::getApplication()->input->setVar('view','club');
					JFactory::getApplication()->input->setVar('edit',true);

					// Checkout the club
					$model=$this->getModel('club');
					$model->checkout();
				} break;
		}
		parent::display();
        */
	}

	/**
	 * sportsmanagementControllerEditClub::save()
	 * 
	 * @return void
	 */
	function save()
	{
		$app = JFactory::getApplication();
    // Check for request forgeries
		JFactory::getApplication()->input->checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$msg='';
		$address_parts = array();
		$post=JFactory::getApplication()->input->get('post');
		
		//$app->enqueueMessage(JText::_('post -> '.'<pre>'.print_r($post,true).'</pre>' ),'');
		
		$cid=JFactory::getApplication()->input->getVar('cid',array(0),'post','array');
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
			$address_parts[] = JSMCountries::getShortCountryName($post['country']);
		}
		$address = implode(', ', $address_parts);
		//$coords = $model->resolveLocation($address);
        $coords = sportsmanagementHelper::resolveLocation($address);
		
		//$app->enqueueMessage(JText::_('coords -> '.'<pre>'.print_r($coords,true).'</pre>' ),'');
		
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
		
    //$app->enqueueMessage(JText::_('post -> '.'<pre>'.print_r($post,true).'</pre>' ),'');
		
		if ($model->save($post))
		{
			$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_SAVED');
			$createTeam=JFactory::getApplication()->input->getVar('createTeam');
			if ($createTeam)
			{
				$team_name=JFactory::getApplication()->input->getVar('name');
				$team_short_name=strtoupper(substr(ereg_replace("[^a-zA-Z]","",$team_name),0,3));
				$teammodel=$this->getModel('team');
				$tpost['id']= "0";
				$tpost['name']= $team_name;
				$tpost['short_name']= $team_short_name ;
				$tpost['club_id']= $teammodel->getDbo()->insertid();
				$teammodel->save($tpost);
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
        
		
        if ($this->getTask()=='save')
		{
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
		}
		else
		{
            $this->setRedirect('index.php?option=com_sportsmanagement&close='.JFactory::getApplication()->input->getString('close', 0).'&tmpl=component&view=editclub&cid='.$post['id'],$msg,$type);
		}
     
        
        
	}

	
}
?>