<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class sportsmanagementViewPlayground extends JView
{
	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document= JFactory::getDocument();

		// Set page title
		//$document->setTitle( JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_TITLE' ) );

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());

		$this->assign( 'project', sportsmanagementModelProject::getProject() );
		$this->assign( 'overallconfig', sportsmanagementModelProject::getOverallConfig() );
		$this->assignRef( 'config', $config );

		//$model = $this->getModel();
		$games = $model->getNextGames();
		$gamesteams = sportsmanagementModelTeams::getTeamsFromMatches( $games );
		$this->assignRef( 'playground',  $model->getPlayground() );
		$this->assignRef( 'teams', sportsmanagementModelTeams::getTeams($this->playground->id) );
		$this->assignRef( 'games', $games );
		$this->assignRef( 'gamesteams', $gamesteams );

		//$this->assignRef( 'mapconfig', $model->getMapConfig() );
		//$this->assignRef( 'address_string', $model->getAddressString() );

		//$this->assignRef( 'gmap', $model->getGoogleMap( $this->mapconfig, $this->address_string ) );
        
		// $gm = $this->getModel( 'googlemap' );
		// $this->assignRef('gm', $gm->getGoogleMap( $model->getMapConfig(), $model->getAddressString() ) );
        
        // diddipoeler
        //$this->geo = new simpleGMapGeocoder();
        //$this->geo->genkml3file($this->playground->id,$this->address_string,'playground',$this->playground->picture,$this->playground->name);

		$extended = sportsmanagementHelper::getExtended($this->playground->extended, 'playground');
		$this->assignRef( 'extended', $extended );
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_PAGE_TITLE' );
		if ( isset( $this->playground->name ) )
		{
			$pageTitle .= ' - ' . $this->playground->name;
		}
		$document->setTitle( $pageTitle );
		$document->addCustomTag( '<meta property="og:title" content="' . $this->playground->name .'"/>' );
		$document->addCustomTag( '<meta property="og:street-address" content="' . $this->address_string .'"/>' );
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		parent::display( $tpl );
	}
}
?>