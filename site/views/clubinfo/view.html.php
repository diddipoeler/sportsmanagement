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

//require_once( JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php' );

/**
 * sportsmanagementViewClubInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewClubInfo extends JViewLegacy
{

	/**
	 * sportsmanagementViewClubInfo::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
		$model			= $this->getModel();
		$club			= $model->getClub() ;
		
		$config			= sportsmanagementModelProject::getTemplateConfig( $this->getName(),$model::$cfg_which_database );	
		$project 		= sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$overallconfig	= sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		$teams			= $model->getTeamsByClubId();
		$stadiums	 	= $model->getStadiums();
		$playgrounds	= $model->getPlaygrounds();
		$isEditor		= sportsmanagementModelProject::hasEditPermission('club.edit');
		$address_string = $model->getAddressString();
//		$map_config		= sportsmanagementModelProject::getMapConfig();
//		$google_map		= $model->getGoogleMap( $map_config, $address_string );
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
                        
        //$app->enqueueMessage(JText::_('clubinfo checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
		
        if ( $this->checkextrafields )
        {
            $this->assignRef( 'extrafields', sportsmanagementHelper::getUserExtraFields($club->id) );
        }
        
		$lat ='';
    $lng ='';
		
		$this->assignRef( 'project',		$project );
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project<br><pre>'.print_r($this->project,true).'</pre>'),'');
        
		$this->assignRef( 'overallconfig',	$overallconfig );
		$this->assignRef( 'config',			$config );

		$this->assignRef( 'showclubconfig',	$showclubconfig );
		$this->assignRef( 'club',			$club);
		$clubassoc			= $model->getClubAssociation($this->club->associations) ;
		$this->assignRef( 'clubassoc',			$clubassoc);

		$extended = sportsmanagementHelper::getExtended($club->extended, 'club');
		$this->assignRef( 'extended', $extended );
        $this->assignRef('model',				$model);

		$this->assignRef( 'teams',			$teams );
		$this->assignRef( 'stadiums',		$stadiums );
		$this->assignRef( 'playgrounds',	$playgrounds );
		$this->assignRef( 'showediticon',	$isEditor );

		$this->assignRef( 'address_string', $address_string);
		$this->assignRef( 'mapconfig',		$map_config ); // Loads the project-template -settings for the GoogleMap

		//$this->assignRef( 'gmap',			$google_map );

    if ( ($this->config['show_club_rssfeed']) == 1 )
	  {
    $mod_name               = "mod_jw_srfr";
    $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_CLUB_RSS_FEED');
    
    //echo 'rssfeed<br><pre>'.print_r($rssfeedlink,true).'</pre><br>';
    if ( $rssfeedlink )
    {
    $this->assignRef( 'rssfeeditems', $model->getRssFeeds($rssfeedlink,$this->overallconfig['rssitems']) );
    }
    else
    {
    $this->assignRef( 'rssfeeditems', $rssfeeditems );
    }
    
    
    }
    

	
        if (($this->config['show_maps'])==1)
	  {
            // diddipoeler
        $this->geo = new JSMsimpleGMapGeocoder();
        $this->geo->genkml3file($this->club->id,$this->address_string,'club',$this->club->logo_big,$this->club->name,$this->club->latitude,$this->club->longitude);  
}

    $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    //$this->assign('use_joomlaworks', JComponentHelper::getParams($option)->get('use_joomlaworks',0) );
    
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE' );
		if ( isset( $this->club ) )
		{
			$pageTitle .= ': ' . $this->club->name;
		}
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' overallconfig<br><pre>'.print_r($this->overallconfig,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');
        
        $this->assignRef( 'modid',$this->club->id );
		// clubhistory
        $this->assign('clubhistory',$model->getClubHistory($this->club->id) );
		$this->assign('clubhistoryhtml',$model->getClubHistoryHTML($this->club->id) );
    // clubhistorytree
		$this->assign('clubhistorytree',$model->getClubHistoryTree($this->club->id,$this->club->new_club_id) );
		$this->assign('clubhistorysorttree',$model->getSortClubHistoryTree($this->clubhistorytree,$this->club->id,$this->club->name) );
        
        $historyobj = $model::$historyobj;
        
        if ( !$historyobj )
        {
        $this->clubhistorysorttree = '';    
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' historyobj<br><pre>'.print_r($historyobj,true).'</pre>'),'');
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistory<br><pre>'.print_r($this->clubhistory,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistoryhtml<br><pre>'.print_r($this->clubhistoryhtml,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistorytree<br><pre>'.print_r($this->clubhistorytree,true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubhistorysorttree<br><pre>'.print_r($this->clubhistorysorttree,true).'</pre>'),'');
        
        $document->addScript( JURI::base().'components/'.$option.'/assets/js/dtree.js' );        
        $document->addStyleSheet(JURI::base().'components/'.$option.'/assets/css/dtree.css');  
    
        $document->setTitle( $pageTitle );
        $view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		parent::display( $tpl );
	}
}
?>