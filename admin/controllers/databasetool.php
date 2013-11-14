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

/**
 * Joomleague Component DatabaseTool Controller
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.0a
 */
class sportsmanagementControllerDatabaseTool extends JController
{

	function __construct()
	{
		parent::__construct();

		$this->registerTask('repair','repair');
		$this->registerTask('optimize','optimize');
        $this->registerTask('picturepath','picturepath');
        $this->registerTask('updatetemplatemasters','updatetemplatemasters');
	}

	function display()
	{
		parent::display();
	}

	function optimize()
	{
		$model=$this->getModel('databasetools');
		if ($model->optimize())
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_OPTIMIZE');
		}
		else
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_ERROR_OPTIMIZE').$model->getError();
		}
		$link='index.php?option=com_sportsmanagement&view=databasetools';
		$this->setRedirect($link,$msg);
	}

	function repair()
	{
		$model=$this->getModel('databasetools');
		if ($model->repair())
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_REPAIR');
		}
		else
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_ERROR_REPAIR').$model->getError();
		}
		$link='index.php?option=com_sportsmanagement&view=databasetools';
		$this->setRedirect($link,$msg);
	}
    
    function picturepath()
	{
		$model=$this->getModel('databasetools');
		if ($model->picturepath())
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_PICTURE_PATH_MIGRATION');
		}
		else
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_ERROR_PICTURE_PATH_MIGRATION').$model->getError();
		}
		$link='index.php?option=com_sportsmanagement&view=databasetools';
		$this->setRedirect($link,$msg);
	}
    
    function updatetemplatemasters()
	{
		$model=$this->getModel('databasetools');
		if ($model->updatetemplatemasters())
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_UPDATE_TEMPLATE_MASTERS');
		}
		else
		{
			$msg=JText::_('COM_JOOMLEAGUE_ADMIN_DBTOOL_CTRL_ERROR_UPDATE_TEMPLATE_MASTERS').$model->getError();
		}
		$link='index.php?option=com_sportsmanagement&view=databasetools';
		$this->setRedirect($link,$msg);
	}

}
?>