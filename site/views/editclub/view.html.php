<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');


class sportsmanagementViewEditClub extends JView
{

	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
        $document = JFactory::getDocument();
		//$version = urlencode(JoomleagueHelper::getVersion());
		//$css='components/com_joomleague/assets/css/tabs.css?v='.$version;
		//$document->addStyleSheet($css);
        
		$model	= $this->getModel();
        $this->assignRef('club',$model->getClub() );

		$lists = array();

    $this->club->merge_teams = explode(",", $this->club->merge_teams);
    

		$this->assignRef('form',$this->get('Form'));	

/*        
        // extended club data
		$xmlfile = JLG_PATH_ADMIN . DS . 'assets' . DS . 'extended' . DS . 'club.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($this->club->extended, 'ini');
		$extended =& JForm::getInstance('extended', $xmlfile, array('control'=> 'extended'), false, '/config');
		$extended->bind($jRegistry);
		$this->assignRef( 'extended', $extended );
*/        

		$this->assignRef('lists',$lists);


//        $this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_joomleague')->get('cfg_which_media_tool',0) );
//        $this->assign('cfg_be_show_merge_teams', JComponentHelper::getParams('com_joomleague')->get('cfg_be_show_merge_teams',0) );

		
		parent::display($tpl);	
	}

	
}
?>
