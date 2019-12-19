<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treetonode.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */
 
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerTreetonode
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerTreetonode extends FormController
{

	/**
	 * sportsmanagementControllerTreetonode::__construct()
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
        $this->jsmdocument = Factory::getDocument();
       
        $this->jsmapp->setUserState($this->jsmoption.'.pid',$this->jsmjinput->get('pid') );
        $this->jsmapp->setUserState($this->jsmoption.'.tid',$this->jsmjinput->get('tid') );
        
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

/**
 * sportsmanagementControllerTreetonode::save()
 * 
 * @param mixed $key
 * @param mixed $urlVar
 * @return void
 */
	/*
function save($key = NULL, $urlVar = NULL)
	{
       $data = $this->jsmjinput->post->getArray();
   			$pid = $this->jsmapp->getUserState( $this->jsmoption . '.pid' );
            $tid = $this->jsmapp->getUserState( $this->jsmoption . '.tid' );
       
       $model = $this->getModel('treetonode');
       $data['id'] = $this->jsmjinput->get('id');
       $result = $model->save($data);
       
       switch ($this->getTask())
       {
       case 'apply':
       if($result)
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
       $link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.edit&id='.$this->jsmjinput->get('id').'&tid='.$tid.'&pid='.$pid;
       break;
       case 'save':
		if($result)
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
        $link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid='.$tid.'&pid='.$pid;
       break;  
        
       }
		$this->setRedirect($link,$msg);
    
    }
*/


public function saveallleaf()
{
/** Check for token */
Session::checkToken() or jexit(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
$cid = $this->jsmjinput->get('cid',array(),'array');
ArrayHelper::toInteger($cid);
$post = $this->jsmjinput->post->getArray(array());
$post['treeto_id'] = (int) $this->jsmjinput->get('tid');
$model = $this->getModel('treetonodes');

if($model->storeshortleaf($cid,$post))
{
$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
}
else
{
$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
}	
	
if($model->storefinishleaf($post))
{
$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_SAVED');
}
else
{
$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_ERROR_SAVED');
}
$link = 'index.php?option=com_sportsmanagement&view=treetonodes&tid='.$this->jsmjinput->get('tid').'&pid='.$this->jsmjinput->get('pid');
$this->setRedirect($link,$msg);	
	
	
	
}
	
	/**
	 * sportsmanagementControllerTreetonode::removenode()
	 * 
	 * @return void
	 */
	public function removenode()
	{
		$post = $this->jsmjinput->post->getArray();
		$model = $this->getModel('treetonodes');
		if($model->setRemoveNode($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_REMOVENODE');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_REMOVENODE');
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
		Session::checkToken() or jexit(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
        $cid = $this->jsmjinput->get('cid',array(),'array');
		ArrayHelper::toInteger($cid);
		$post = $this->jsmjinput->post->getArray(array());
        $post['treeto_id'] = (int) $this->jsmjinput->get('tid');
		$model = $this->getModel('treetonodes');

		if($model->storeshortleaf($cid,$post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
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
		Session::checkToken() or jexit(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
		$cid = $this->jsmjinput->get('cid',array(),'array');
		ArrayHelper::toInteger($cid);
		$post = $this->jsmjinput->post->getArray(array());
        $post['treeto_id'] = (int) $this->jsmjinput->get('tid');

		$model = $this->getModel('treetonodes');
		if($model->storefinishleaf($post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_LEAFS_ERROR_SAVED');
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
		Session::checkToken() or jexit(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));

		$cid = $this->jsmjinput->get('cid',array(),'array');
		ArrayHelper::toInteger($cid);
		$post = $this->jsmjinput->post->getArray(array());

		$model = $this->getModel('treetonodes');
		if($model->storeshort($cid,$post))
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_SAVED');
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError();
		}
		$link = 'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode&tid='.$this->jsmjinput->get('tid').'&pid='.$this->jsmjinput->get('pid');
		$this->setRedirect($link,$msg);
	}
    
}
