<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');


class sportsmanagementControllerDatabaseTools extends JController
{

    /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'databasetool', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}
?>