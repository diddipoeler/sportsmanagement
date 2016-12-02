<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
                        
        //$app->enqueueMessage(JText::_('clubinfo checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
		
        if ( $this->checkextrafields )
        {
            $this->extrafields = sportsmanagementHelper::getUserExtraFields($club->id,'frontend',sportsmanagementModelClubInfo::$cfg_which_database);
        }
        
		$lat ='';
    $lng ='';
		
		$this->club = sportsmanagementModelClubInfo::getClub(1);
		$this->clubassoc = sportsmanagementModelClubInfo::getClubAssociation($this->club->associations);
		$this->extended = sportsmanagementHelper::getExtended($this->club->extended, 'club');
		$this->teams = sportsmanagementModelClubInfo::getTeamsByClubId();
		$this->stadiums = sportsmanagementModelClubInfo::getStadiums();
		$this->playgrounds = sportsmanagementModelClubInfo::getPlaygrounds();
		$this->showediticon = sportsmanagementModelProject::hasEditPermission('club.edit');
		$this->address_string = sportsmanagementModelClubInfo::getAddressString();

    if ( $this->config['show_club_rssfeed'] )
	  {
    $mod_name               = "mod_jw_srfr";
    $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_CLUB_RSS_FEED');
    
    //echo 'rssfeed<br><pre>'.print_r($rssfeedlink,true).'</pre><br>';
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
            // diddipoeler
        $this->geo = new JSMsimpleGMapGeocoder();
        $this->geo->genkml3file($this->club->id,$this->address_string,'club',$this->club->logo_big,$this->club->name,$this->club->latitude,$this->club->longitude);  
}

    $this->show_debug_info = JComponentHelper::getParams($this->option)->get('show_debug_info',0);
    //$this->assign('use_joomlaworks', JComponentHelper::getParams($option)->get('use_joomlaworks',0) );
    
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE' );
		if ( isset( $this->club ) )
		{
			$pageTitle .= ': ' . $this->club->name;
		}
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' overallconfig<br><pre>'.print_r($this->overallconfig,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');
        
        $this->modid = $this->club->id;
		// clubhistory
        $this->clubhistory = sportsmanagementModelClubInfo::getClubHistory($this->club->id);
		$this->clubhistoryhtml = sportsmanagementModelClubInfo::getClubHistoryHTML($this->club->id);
        
//        $this->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistoryhtml<br><pre>'.print_r($this->clubhistoryhtml,true).'</pre>'),'');
$this->clubhistoryfamilytree = sportsmanagementModelClubInfo::fbTreeRecurse($this->club->id, '', array (),sportsmanagementModelClubInfo::$tree_fusion, 10, 0, 1);
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistoryfamilytree<br><pre>'.print_r($this->clubhistoryfamilytree,true).'</pre>'),'');        
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tree<br><pre>'.print_r(sportsmanagementModelClubInfo::$tree_fusion,true).'</pre>'),'');        
//        $this->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistoryhtml<br><pre>'.print_r($this->clubhistoryhtml,true).'</pre>'),'');        
    // clubhistorytree
		$this->clubhistorytree = sportsmanagementModelClubInfo::getClubHistoryTree($this->club->id,$this->club->new_club_id);
		$this->clubhistorysorttree = sportsmanagementModelClubInfo::getSortClubHistoryTree($this->clubhistorytree,$this->club->id,$this->club->name);
        
        $historyobj = sportsmanagementModelClubInfo::$historyobj;
        
        if ( !$historyobj )
        {
        $this->clubhistorysorttree = '';    
        }
        
        $this->document->addScript( JURI::base().'components/'.$this->option.'/assets/js/dtree.js' );        
        $this->document->addStyleSheet(JURI::base().'components/'.$this->option.'/assets/css/dtree.css');  
    
        $this->document->addScript( JURI::base().'components/'.$this->option.'/assets/js/bootstrap-tree.js' ); 
        $this->document->addStyleSheet(JURI::base().'components/'.$this->option.'/assets/css/bootstrap-combined.min.css');
        $this->document->addStyleSheet(JURI::base().'components/'.$this->option.'/assets/css/bootstrap-tree.css');  
        
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
