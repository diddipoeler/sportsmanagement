<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');


class sportsmanagementControllercurrentseason extends JController
{
	protected $view_list = 'currentseasons';
    function __construct()
	{
		parent::__construct();


	}
    
    function display()
	{
	
		parent::display();
	}

}

?>    