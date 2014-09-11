<?php 

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php' );

jimport( 'joomla.application.component.view' );

class JoomleagueViewReferees extends JLGView
{

	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();

		$model	= $this->getModel();
		$config = $model->getTemplateConfig($this->getName());
		
		if ( !$config )
		{
			$config	= $model->getTemplateConfig( 'players' );
		}

		$this->assignRef( 'project', $model->getProject() );
		$this->assignRef( 'overallconfig', $model->getOverallConfig() );
		$this->assignRef( 'config', $config );

		$this->assignRef( 'rows', $model->getReferees() );
//		$this->assignRef( 'positioneventtypes', $model->getPositionEventTypes( ) );

		// Set page title
		$pagetitle=JText::_( 'COM_JOOMLEAGUE_REFEREES_PAGE_TITLE' );
		$document->setTitle( JText::sprintf( $pagetitle, $this->project->name ) );

		parent::display( $tpl );
	}

}
?>