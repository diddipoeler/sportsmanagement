<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * HelloWorld Controller
 */
class sportsmanagementControllerplayground extends JControllerForm
{

function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('apply','save');
	}
  
  
function save()
	{
  // Check for request forgeries
		JRequest::checkToken() or die('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN');

		$post		= JRequest::get('post');
 		$pid		= JRequest::getInt('id');
 		//$post['id'] = $pid; //map cid to table pk: id
    
    $post = JRequest::getVar('jform', array(), 'post', 'array');
		
		// decription must be fetched without striping away html code
		$post['notes'] = JRequest:: getVar('notes','none','post','STRING',JREQUEST_ALLOWHTML);

		$model = $this->getModel('playground');
    
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
    
    foreach( $coords as $key => $value )
		{
    $post['extended'][$key] = $value;
    }
		
		$post['latitude'] = $coords['latitude'];
		$post['longitude'] = $coords['longitude'];
		
		if ($model->save($post))
		{
			$msg = JText::_('COM_JOOMLEAGUE_ADMIN_PLAYGROUND_CTRL_SAVED');
    }
    else
		{
			$msg = JText::_('COM_JOOMLEAGUE_ADMIN_PLAYGROUND_CTRL_ERROR_SAVE').$model->getError();
		}
    
    //-------extra fields-----------//
    //$model->saveExtraFields($_POST,$pid);
		
      
    if ($this->getTask() == 'save')
		{
			$link = 'index.php?option=com_sportsmanagement&view=playgrounds';
		}
		else
		{
			$link = 'index.php?option=com_sportsmanagement&view=playground&layout=edit&id='.$pid;
		}
		#echo $msg;
		$this->setRedirect($link,$msg);
  
  
  }


  
}
