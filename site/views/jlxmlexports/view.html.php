<?php defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;

jimport( 'joomla.application.component.view');

class sportsmanagementViewjlxmlexports extends JViewLegacy
{
	function display( $tpl = null )
	{
		
    // Get a refrence of the page instance in joomla
		$document = & Factory::getDocument();
		$uri = &Factory::getURI();		
				
		
    
    //$model =& $this->getModel( 'jlxmlexports' ); 
    $model =& $this->getModel();
    
    /*
    echo '<pre>';
		print_r($model);
		echo '</pre>';
		*/
		
    $model->exportData();

		parent::display( $tpl );
	}
}
?>