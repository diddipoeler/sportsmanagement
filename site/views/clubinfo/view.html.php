<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewClubInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewClubInfo extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewClubInfo::init()
	 * 
	 * @return void
	 */
	function init()
	{
        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend',sportsmanagementModelClubInfo::$cfg_which_database);
                        
		$this->club = sportsmanagementModelClubInfo::getClub(1);
        if ( $this->checkextrafields )
        {
            $this->extrafields = sportsmanagementHelper::getUserExtraFields($this->club->id,'frontend',sportsmanagementModelClubInfo::$cfg_which_database);
        }
        
		$lat ='';
		$lng ='';
		
		$this->clubassoc = sportsmanagementModelClubInfo::getClubAssociation($this->club->associations);
		$this->extended = sportsmanagementHelper::getExtended($this->club->extended, 'club');
		$this->teams = sportsmanagementModelClubInfo::getTeamsByClubId();
		$this->stadiums = sportsmanagementModelClubInfo::getStadiums();
		$this->playgrounds = sportsmanagementModelClubInfo::getPlaygrounds();
		$this->showediticon = sportsmanagementModelProject::hasEditPermission('club.edit');
		$this->address_string = sportsmanagementModelClubInfo::getAddressString();

    if ( $this->config['show_club_rssfeed'] )
	  {
    $mod_name = "mod_jw_srfr";
    $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_CLUB_RSS_FEED');
    
    if ( $rssfeedlink )
    {
    $this->rssfeeditems = sportsmanagementModelClubInfo::getRssFeeds($rssfeedlink,$this->overallconfig['rssitems']);
    }
    else
    {
    $this->rssfeeditems = $rssfeeditems;
    }
        
    }
    
        if ( $this->config['show_maps'] )
	  {
/**
 * diddipoeler
 */
        $this->geo = new JSMsimpleGMapGeocoder();
        $this->geo->genkml3file($this->club->id,$this->address_string,'club',$this->club->logo_big,$this->club->name,$this->club->latitude,$this->club->longitude);  
}

    $this->show_debug_info = JComponentHelper::getParams($this->option)->get('show_debug_info',0);
    
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE' );
		if ( isset( $this->club ) )
		{
			$pageTitle .= ': ' . $this->club->name;
		}
		
        $this->modid = $this->club->id;
/**
 * clubhistory
 */
        $this->clubhistory = sportsmanagementModelClubInfo::getClubHistory($this->club->id);
		$this->clubhistoryhtml = sportsmanagementModelClubInfo::getClubHistoryHTML($this->club->id);
        
$this->clubhistoryfamilytree = sportsmanagementModelClubInfo::fbTreeRecurse($this->club->id, '', array (),sportsmanagementModelClubInfo::$tree_fusion, 10, 0, 1);
$this->genfamilytree = sportsmanagementModelClubInfo::generateTree($this->club->id);
$this->familytree = sportsmanagementModelClubInfo::$historyhtmltree;
       
/**
 * clubhistorytree
 */
		$this->clubhistorytree = sportsmanagementModelClubInfo::getClubHistoryTree($this->club->id,$this->club->new_club_id);
		$this->clubhistorysorttree = sportsmanagementModelClubInfo::getSortClubHistoryTree($this->clubhistorytree,$this->club->id,$this->club->name);
        
        $historyobj = sportsmanagementModelClubInfo::$historyobj;
        
        if ( !$historyobj )
        {
        $this->clubhistorysorttree = '';    
        }

        $this->document->addStyleSheet(JURI::base().'components/'.$this->option.'/assets/css/bootstrap-familytree.css');
        
        $this->document->setTitle( $pageTitle );
        
/**
 *         da wir komplett mit bootstrap arbeiten benötigen wir das nicht mehr 
 *         $view = $jinput->getVar( "view") ;
 *         $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 *         $document->addCustomTag($stylelink);
 */
        
        if ( !isset($this->config['table_class']) )
        {
            $this->config['table_class'] = 'table';
        }
        
	}
}
?>
