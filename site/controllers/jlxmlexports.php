<?php defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class sportsmanagementControllerjlxmlexports extends JController
{
    function display( )
    {
        //$this->showprojectheading();
        $this->showranking();
        //$this->showbackbutton();
        //$this->showfooter();
    }

    function showranking( )
    {
        /*
        // Get the view name from the query string
        $viewName = JRequest::getVar( "view", "jlxmlexports" );

        // Get the view
        $view = & $this->getView( $viewName );

        //$this->addModelPath( JPATH_SITE . 'administrator' . DS . 'components' . DS . 'com_joomleague' . DS . 'models');
        $this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_joomleague' . DS . 'models');
		$view->setModel( $this->getModel( 'jlxmlexports', 'ErweiterungModel' ), true );
		
		echo 'JoomleagueControllerjlxmlexports <br>';
		echo '<pre>';
		print_r($view);
		echo '</pre>';
		
    //$model = $this->getModel( 'jlxmlexports' );
    
        
        $view->display();
        */
    }
}
