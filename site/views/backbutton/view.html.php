<?php defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

class sportsmanagementViewBackButton extends JView
{
	function display( $tpl = null )
	{
		//$model = $this->getModel();

		//$this->assignRef( 'project',		$model->getProject() );
		//$this->assignRef( 'overallconfig',	$model->getOverallConfig() );

		parent::display( $tpl );
	}
}
?>