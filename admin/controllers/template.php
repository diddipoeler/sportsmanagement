<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      template.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllertemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllertemplate extends JControllerForm
{

/**
 * sportsmanagementControllertemplate::__construct()
 * 
 * @return void
 */
function __construct()
	{
		$app	= JFactory::getApplication();
		$option = JFactory::getApplication()->input->getCmd('option');
		parent::__construct();

	
		// Register Extra tasks
		$this->registerTask('reset','remove');
	}
    
/**
 * sportsmanagementControllertemplate::remove()
 * 
 * @return
 */
function remove()
	{
		$cid = JFactory::getApplication()->input->getVar('cid',array(0),'post','array');
		JArrayHelper::toInteger($cid);
		$isMaster = JFactory::getApplication()->input->getVar('isMaster',array(),'post','array');
		JArrayHelper::toInteger($isMaster);
		if (count($cid) < 1){
			JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_DELETE'));
		}
		foreach ($cid AS $id)
		{
			if ($isMaster[$id])
			{
				echo "<script> alert('" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_DELETE_WARNING') . "'); window.history.go(-1); </script>\n";
				return;
			}
		}
		$model = $this->getModel('template');
		if (!$model->delete($cid))
		{
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$msg = JText::_("COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_RESET_SUCCESS");
		$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid='.JFactory::getApplication()->input->getInt( "pid", 0 ), $msg);
	}
	
	/**
	 * sportsmanagementControllertemplate::masterimport()
	 * 
	 * @return void
	 */
	function masterimport()
{
$templateid = JFactory::getApplication()->input->getVar('templateid',0,'post','int');
$projectid = JFactory::getApplication()->input->getVar('pid',0,'post','int');
$model = $this->getModel('template');
if ( $templateid )
{
if ($model->import($templateid,$projectid))
{
$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_IMPORTED_TEMPLATE');
}
else
{
$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_ERROR_IMPORT_TEMPLATE').$model->getError();
}

}
$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid='.$projectid,$msg);
}	

}
