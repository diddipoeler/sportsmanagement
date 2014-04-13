<?php defined( '_JEXEC' ) or die( 'Restricted access' );



jimport( 'joomla.application.component.view');

class sportsmanagementViewjlxmlexports extends JView
{
	function display( $tpl = null )
	{
		
    // Get a refrence of the page instance in joomla
		$document = & JFactory::getDocument();
		$uri = &JFactory::getURI();		
				
		
    
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