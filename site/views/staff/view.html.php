<?php 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class sportsmanagementViewStaff extends JView
{

	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
        $mainframe = JFactory::getApplication();

		$model = $this->getModel();
//        $mdlPerson = JModel::getInstance("Person", "sportsmanagementModel");
        $model->projectid = JRequest::getInt( 'p', 0 );
		$model->personid = JRequest::getInt( 'pid', 0 );
		$model->teamplayerid = JRequest::getInt( 'pt', 0 );
//        $mdlPerson->projectid = JRequest::getInt( 'p', 0 );
//		$mdlPerson->personid = JRequest::getInt( 'pid', 0 );
//		$mdlPerson->teamplayerid = JRequest::getInt( 'pt', 0 );
        
//      sportsmanagementModelPerson::projectid = JRequest::getInt( 'p', 0 );
//		sportsmanagementModelPerson::personid = JRequest::getInt( 'pid', 0 );
//		sportsmanagementModelPerson::teamplayerid = JRequest::getInt( 'pt', 0 );
        
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$person = sportsmanagementModelPerson::getPerson();
        
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewStaff person<br><pre>'.print_r($person,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewStaff personid<br><pre>'.print_r($model->personid,true).'</pre>'),'');

		$this->assign('project',sportsmanagementModelProject::getProject());
		$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig());
		$this->assignRef('config',$config);
		$this->assignRef('person',$person);
		$this->assign('showediticon',sportsmanagementModelPerson::getAllowed($config['edit_own_player']));
		
		$staff=&$model->getTeamStaff();
		$titleStr=JText::sprintf('COM_SPORTSMANAGEMENT_STAFF_ABOUT_AS_A_STAFF', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));		
		
		$this->assignRef('inprojectinfo',$staff);
        
		$this->assign('history',$model->getStaffHistory('ASC'));

		$this->assign('stats',$model->getStats());
		$this->assign('staffstats',$model->getStaffStats());
		$this->assign('historystats',$model->getHistoryStaffStats());
		$this->assign('title',$titleStr);

		$extended = sportsmanagementHelper::getExtended($person->extended, 'staff');
		$this->assignRef( 'extended', $extended);
		$document->setTitle($titleStr);
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        

		parent::display($tpl);
	}

}
?>