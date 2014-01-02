<?php defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');


class sportsmanagementViewProjectHeading extends JView
{
    function display( $tpl = null )
    {
        /*
        $model = $this->getModel();
        $this->assignRef( 'project', $model->getProject() );
		$this->assignRef( 'division', $model->getDivision(JRequest::getInt('division', 10)) );
        $this->assignRef( 'overallconfig', $model->getOverallConfig() );
        $this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
        
        if ( $this->overallconfig['show_project_sporttype_picture'] )
        {
        
        }
        */
        parent::display($tpl);
    }
}
?>