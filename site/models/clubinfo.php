<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );
//include_once JPATH_COMPONENT . DS . 'helpers' . DS . 'easygooglemap.php';

/**
 * sportsmanagementModelClubInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelClubInfo extends JModelLegacy
{
	var $projectid = 0;
	var $clubid = 0;
	static $club = null;
    
    var $new_club_id = 0;
    var $historyhtml = '';
	var $historyobj = array();

  var $catssorted = array();
  	
  var $jgcat_rows = array();
  var $jgcat_rows_sorted = Array();
     
	var $treedepth = 0;
	var $treedepthold = 0;
    
    static $cfg_which_database = 0;	

	/**
	 * sportsmanagementModelClubInfo::__construct()
	 * 
	 * @return void
	 */
	function __construct( )
	{
		

		$this->projectid = JRequest::getInt( "p", 0 );
		$this->clubid = JRequest::getInt( "cid", 0 );
        
        sportsmanagementModelProject::$projectid = $this->projectid;
        self::$cfg_which_database = JRequest::getInt('cfg_which_database',0);
        
        parent::__construct( );
	}

	// limit count word
	/**
	 * sportsmanagementModelClubInfo::limitText()
	 * 
	 * @param mixed $text
	 * @param mixed $wordcount
	 * @return
	 */
	function limitText($text, $wordcount)
	{
		if(!$wordcount) {
			return $text;
		}

		$texts = explode( ' ', $text );
		$count = count( $texts );

		if ( $count > $wordcount )
		{
			$texts = array_slice($texts,0, $wordcount ) ;
			$text = implode(' ' , $texts);
			$text .= '...';
		}

		return $text;
	}
    
    /**
     * sportsmanagementModelClubInfo::getRssFeeds()
     * 
     * @param mixed $rssfeedlink
     * @param mixed $rssitems
     * @return
     */
    function getRssFeeds($rssfeedlink,$rssitems)
    {
    $rssIds	= array();    
    $rssIds = explode(',',$rssfeedlink);    
    //  get RSS parsed object
		$options = array();
        $options['cache_time'] = null;
        
        $lists = array();
		foreach ($rssIds as $rssId)
		{
		$options['rssUrl'] 		= $rssId; 
        
        $rssDoc =& JFactory::getXMLparser('RSS', $options);
		$feed = new stdclass();
        if ($rssDoc != false)
			{
				// channel header and link
				$feed->title = $rssDoc->get_title();
				$feed->link = $rssDoc->get_link();
				$feed->description = $rssDoc->get_description();
	
				// channel image if exists
				$feed->image->url = $rssDoc->get_image_url();
				$feed->image->title = $rssDoc->get_image_title();
	
				// items
				$items = $rssDoc->get_items();
				// feed elements
				$feed->items = array_slice($items, 0, $rssitems);
				$lists[] = $feed;
			}
        
         
        }  
    //var_dump($lists);
    //echo 'getRssFeeds lists<pre>',print_r($lists,true),'</pre><br>';
    return $lists;         
    }
    
    /**
     * sportsmanagementModelClubInfo::getClubAssociation()
     * 
     * @param mixed $associations
     * @return
     */
    function getClubAssociation($associations)
	{
	   $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        // Select some fields
             $query->select('asoc.*');
             // From 
		     $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations AS asoc');
             // Where
             $query->where('asoc.id = '. $db->Quote($associations) );

				$db->setQuery($query);
				$result = $db->loadObject();
	
		return $result;
	}
	
    /**
     * sportsmanagementModelClubInfo::getClub()
     * 
     * @return
     */
    function getClub( )
	{
		$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
        $this->projectid = JRequest::getInt( "p", 0 );
		$this->clubid = JRequest::getInt( "cid", 0 );
        if ( is_null( self::$club ) )
		{
			if ( $this->clubid > 0 )
			{
			 // Select some fields
             $query->select('c.*');
             // From 
		     $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c');
             // Where
             $query->where('c.id = '. $db->Quote($this->clubid) );

				$db->setQuery($query);
				self::$club = $db->loadObject();
			}
		}
		return self::$club;
	}

	/**
	 * sportsmanagementModelClubInfo::getTeamsByClubId()
	 * 
	 * @return
	 */
	function getTeamsByClubId()
	{
		$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $subquery1 = $db->getQuery(true);
        $subquery2 = $db->getQuery(true);
        
        $teams = array( 0 );
		if ( $this->clubid > 0 )
		{
		  // Select some fields
          $query->select('t.id,prot.trikot_home,prot.trikot_away');
          $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
          $query->select('t.name as team_name,t.short_name as team_shortcut,t.info as team_description');
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t ');
          $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = t.id');
          $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as prot ON prot.team_id = st.id ');
          
          // Select some fields
          $subquery1->select('CONCAT_WS( \':\', MAX(pt.project_id) , p.alias )');
          // From 
          $subquery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
          $subquery1->join('RIGHT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project p ON pt.project_id = p.id ');
          $subquery1->where('pt.team_id = st.id');
          $subquery1->where('p.published = 1');
          
          // Select some fields
          $subquery2->select('pt.id');
          // From 
          $subquery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
          $subquery2->join('RIGHT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project p ON pt.project_id = p.id ');
          $subquery2->where('pt.team_id = st.id');
          $subquery2->where('p.published = 1');
          $subquery2->where('pt.project_id = pid');
          
          $query->select('('.$subquery1.' ) as pid');
          $query->select('('.$subquery2.' ) as ptid');
          
          $query->where('t.club_id = '.(int) $this->clubid);
          
          $query->group('t.id');

			$db->setQuery( $query );
			$teams = $db->loadObjectList();
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');
            
            if ( !$teams )
            {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
            }

		}
		return $teams;
	}

	/**
	 * sportsmanagementModelClubInfo::getStadiums()
	 * 
	 * @return
	 */
	function getStadiums()
	{
		$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
        $stadiums = array();

		$club = self::getClub();
		if ( !isset( $club ) )
		{
			return null;
		}
		if ( $club->standard_playground > 0 )
		{
			$stadiums[] = $club->standard_playground;
		}
		$teams = self::getTeamsByClubId();

		if ( count( $teams > 0 ) )
		{
			foreach ($teams AS $team )
			{
			 $query->clear();
             // Select some fields
             $query->select('distinct(pt.standard_playground)');
             // From 
		     $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
             $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id');
                // Where
                $query->where('st.team_id = '.(int)$team->id );
                $query->where('pt.standard_playground > 0');
                           
				if ( $club->standard_playground > 0 )
				{
				    // Where
                    $query->where('standard_playground <> '. $club->standard_playground );
				}
				$db->setQuery($query);
				if ( $res = $db->loadResult() )
				{
					$stadiums[] = $res;
				}
			}
		}
		return $stadiums;
	}

	/**
	 * sportsmanagementModelClubInfo::getPlaygrounds()
	 * 
	 * @return
	 */
	function getPlaygrounds( )
	{
		$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
        $playgrounds = array();

		$stadiums = self::getStadiums();
		if ( !isset ( $stadiums ) )
		{
			return null;
		}

		foreach ( $stadiums AS $stadium )
		{
		  $query->clear();
          // Select some fields
          $query->select('id AS value, name AS text, pl.*');
          $query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS slug');
          // From 
		  $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS pl');
          // Where
          $query->where('id = '. $stadium );

			$db->setQuery($query, 0, 1);
			$playgrounds[] = $db->loadObject();
		}
		return $playgrounds;
	}
    
    /**
     * sportsmanagementModelClubInfo::getClubHistory()
     * 
     * @param mixed $clubid
     * @return
     */
    function getClubHistory( $clubid )
	{
	$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $query->select('c.id, c.name, c.new_club_id');
        $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ');
        $query->where('c.new_club_id = '. $clubid );
        
//				$query = ' SELECT c.id, c.name, c.new_club_id, '
//				. ' CASE WHEN CHAR_LENGTH( alias ) THEN CONCAT_WS( \':\', id, alias ) ELSE id END AS slug '
//				       . ' FROM #__joomleague_club AS c '
//				       . ' WHERE c.new_club_id = '. $clubid
//				            ;
                            
				$db->setQuery($query);
				$result = $db->loadObjectList();
			
	foreach ( $result as $row )
	{
  $temp = new stdClass();
  $temp->id = $row->id;
	$temp->name = $row->name;
  $temp->slug = $row->slug;	
  $this->historyobj[] = $temp;
  
  if ( $row->new_club_id )
  {
  $this->getClubHistory( $row->id );
  }
  else
  {
  return $this->historyobj;
  }
  
  }

  return $this->historyobj;
  
	}
    
       
        /**
         * sportsmanagementModelClubInfo::getClubHistoryHTML()
         * 
         * @param mixed $clubid
         * @return
         */
        function getClubHistoryHTML( $clubid )
	{
	   $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $subquery = $db->getQuery(true);
        
        $query->select('c.id, c.name, c.new_club_id');
        $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
        
        $subquery->select('max(pt.project_id)');
        $subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
        $subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id');
        $subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
        $subquery->join('RIGHT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on pt.project_id = p.id');
        $subquery->where('t.club_id = c.id ');
        $subquery->where('p.published = 1');
        
        $query->select('('.$subquery.') as pid ');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c');
        
        $query->where('c.new_club_id = '. $clubid );


//$query = ' SELECT c.id, c.name, c.new_club_id, '
//				. ' CASE WHEN CHAR_LENGTH( alias ) THEN CONCAT_WS( \':\', id, alias ) ELSE id END AS slug '
//				. ',(select max(pt.project_id) 
//             from #__joomleague_project_team pt
//             inner join #__joomleague_team t
//             on pt.team_id = t.id   
//             right join #__joomleague_project p 
//             on pt.project_id = p.id 
//             where t.club_id = c.id 
//             and p.published = 1) as pid'
//				       . ' FROM #__joomleague_club AS c '
//				       . ' WHERE c.new_club_id = '. $clubid
//				            ;
				            
				$db->setQuery($query);
				$result = $db->loadObjectList();
			
	foreach ( $result as $row )
	{
  //$temp = '<ul><li>'.$row->name.'</li>';
  //$this->treedepthold = $this->treedepth;
  
  if ( $this->treedepthold === $this->treedepth )
  {
  $temp = '<li>';  
  }
  else
  {
  $temp = '<ul><li>';
  }
  
  
  $link = sportsmanagementHelperRoute::getClubInfoRoute( $row->pid, $row->slug,null,self::$cfg_which_database );	
  $imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM' );
  
  $temp .= JHTML::_(	'image',
														'media/com_sportsmanagement/jl_images/club_from.png',
														$imageTitle,
														'title= "' . $imageTitle . '"' );
	$temp .= "&nbsp;";								
	$temp .= JHTML::link( $link, $row->name );
  $temp .= '</li>';													
  $this->historyhtml .= $temp;
  
  if ( $row->new_club_id )
  {
  $this->treedepth++;
  $this->getClubHistoryHTML( $row->id );
  }
  else
  {
  
  for ($a=0; $a < $this->treedepth;$a++)
  {
  $this->historyhtml .= '</ul>';
  }
  return $this->historyhtml;
  }
  
  $this->treedepthold = $this->treedepth;
  
  }

  return $this->historyhtml;
  
	}
    
    
        /**
         * sportsmanagementModelClubInfo::getClubHistoryTree()
         * 
         * @param mixed $clubid
         * @param mixed $new_club_id
         * @return
         */
        function getClubHistoryTree( $clubid, $new_club_id )
	{
	$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $subquery = $db->getQuery(true);
    
	if ( $this->new_club_id != 0 )
	{
  $icon = 'to_club.png';
//  $querywhere = ' WHERE c.id = '. $this->new_club_id	;
  $query->where('c.id = '. $this->new_club_id );
  }
  else
  {
  $icon = 'from_club.png';
  //$querywhere = ' WHERE c.new_club_id = '. $clubid	;
  $query->where('c.new_club_id = '. $clubid );
  }
  
  $query->select('c.id, c.name, c.new_club_id');
        $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
        
        $subquery->select('max(pt.project_id)');
        $subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
        $subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id');
        $subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
        $subquery->join('RIGHT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on pt.project_id = p.id');
        $subquery->where('t.club_id = c.id ');
        $subquery->where('p.published = 1');
        
        $query->select('('.$subquery.') as pid ');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c');
        
//	$queryselect = ' SELECT c.id, c.name, c.new_club_id, '
//				. ' CASE WHEN CHAR_LENGTH( alias ) THEN CONCAT_WS( \':\', id, alias ) ELSE id END AS slug '
//				. ',(select max(pt.project_id) 
//             from #__joomleague_project_team pt
//             inner join #__joomleague_team t
//             on pt.team_id = t.id   
//             right join #__joomleague_project p 
//             on pt.project_id = p.id 
//             where t.club_id = c.id 
//             and p.published = 1) as pid'
//				       . ' FROM #__joomleague_club AS c ';
				       
	
	
  //$query = $queryselect.$querywhere;
  			            
				$db->setQuery($query);
                
//                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
                
				$result = $db->loadObjectList();
   foreach ( $result as $row )
	 {
   $row->link = sportsmanagementHelperRoute::getClubInfoRoute( $row->pid, $row->slug );
   $row->icon = $icon;
   }
		
  $this->jgcat_rows = array_merge($this->jgcat_rows, $result);
  
  //$app->enqueueMessage(JText::_('jgcat_rows -> '.'<pre>'.print_r($this->jgcat_rows,true).'</pre>' ),'');
  
  foreach ( $result as $row )
	{
	if ( $row->new_club_id )
  {
  $this->treedepth++;
  $this->getClubHistoryTree( $row->id, $row->new_club_id );
  }
  else
  {
    
  return $this->jgcat_rows;
  }
	
	}
  	
	return $this->jgcat_rows;
	
	}
    
    
    
        /**
         * sportsmanagementModelClubInfo::getSortClubHistoryTree()
         * 
         * @param mixed $clubtree
         * @param mixed $root_catid
         * @param mixed $cat_name
         * @return
         */
        function getSortClubHistoryTree( $clubtree, $root_catid, $cat_name )
	{
	$option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
	$script = '';
	
  $jgcat_rows_sorted = Array();
  $jgcat_rows_sorted = $this->sortCategoryList($clubtree, $jgcat_rows_sorted);



  $cat_link = '';
  
  $script .= "d" .$root_catid. " = new dTree('d" .$root_catid. "','".JURI::base()."/components/".$option."/assets/img/standard2/');"."\n";
  $script .= "d" .$root_catid. ".add(" . "0" . ", " . "-1" . ", ";
  $script .= "'" . $cat_name . "', ";
  $script .= "'" . $cat_link . "', ";
  //$script .= "'" . ($aid >= $row->access ? 'false' : 'true') . "');" ."\n";
  $script .= "'" . 'true' . "');" ."\n";
          
  foreach ( $jgcat_rows_sorted as $key => $value )
  {
  
  foreach ( $value as $row )
  {
  
  if ( $root_catid == $row->new_club_id )
  {
  $script .= "d" .$root_catid. ".add(" . $row->id . ", " . "0" . ", ";
  $script .= "'" . $row->name . "', ";
  $script .= "'" . $row->link . "', ";
  //$script .= "'" . ($aid >= $row->access ? 'false' : 'true') . "');" ."\n";
  $script .= "'','" . $row->name . "','','".JURI::base()."/components/".$option."/assets/img/standard2/" . $row->icon . "');" ."\n";
  }
  else
  {
  $script .= "d" .$root_catid. ".add(" . $row->id . ", " . $row->new_club_id. ", ";
  $script .= "'" . $row->name . "', ";
  $script .= "'" . $row->link . "', ";
  //$script .= "'" . ($aid >= $row->access ? 'false' : 'true') . "');" ."\n";
  $script .= "'','" . $row->name . "','','".JURI::base()."/components/".$option."/assets/img/standard2/" . $row->icon . "');" ."\n";
  }
  
  }
  
  }

  $script .= "document.write(d" .$root_catid.  ");" . "\n";
  
	return $script;
	}
    
    
    
    /**
     * sportsmanagementModelClubInfo::sortCategoryListRecurse()
     * 
     * @param mixed $catid
     * @param mixed $children
     * @param mixed $catssorted
     * @return void
     */
    function sortCategoryListRecurse($catid, &$children, &$catssorted)
  {
    if(isset($children[$catid]))
    {
      foreach($children[$catid] as $cat)
      {
        $catssorted[] = $cat;
        $this->sortCategoryListRecurse($cat->cid, $children, $catssorted);
      }
    }
  }
    
    /**
     * sportsmanagementModelClubInfo::sortCategoryList()
     * 
     * @param mixed $cats
     * @param mixed $catssorted
     * @return
     */
    function sortCategoryList(&$cats, &$catssorted)
  {
  

  
    // First create a two dimensional array containing the child category objects
    // for each parent category id
    $children = array();
    foreach($cats as $cat)
    {
      $pcid = $cat->new_club_id;
      $list = isset($children[$pcid]) ? $children[$pcid] : array();
      $list[] = $cat;
      $children[$pcid] = $list;
    }



    // Now resort the given $cats array with the help of the $children array
    $sortresult = $this->sortCategoryListRecurse(0, $children, $catssorted);
    

    
    return $children;
  }
    

/**
 * 	function getGoogleApiKey( )
 * 	{
 * 		$option = JRequest::getCmd('option');
 *         $params = JComponentHelper::getParams($option);
 * 		$apikey=$params->get('cfg_google_api_key');
 * 		return $apikey;
 * 	}
 */

/**
 * 	function getGoogleMap( $mapconfig, $address_string = "" )
 * 	{
 * 		$gm = null;

 * 		$google_api_key = $this->getGoogleApiKey();
 * 		if ( ( trim( $google_api_key ) != "" ) &&
 * 		( trim( $address_string ) != "" ) )
 * 		{
 * 			$gm = new EasyGoogleMap( $google_api_key, "jl_pg_map" );

 * 			$width = ( is_int( $mapconfig['width'] ) ) ? $mapconfig['width'].'px' : $mapconfig['width'];

 * 			$gm->SetMapWidth( $mapconfig['width'] );
 * 			$gm->SetMapHeight( $mapconfig['height'] );
 * 			$gm->SetMapControl( $mapconfig['map_control'] );
 * 			$gm->SetMapDefaultType( $mapconfig['default_map_type'] );

 * 			if ( intval( $mapconfig['map_zoom'] ) > 0 )
 * 			{
 * 				$gm->SetMapZoom( intval( $mapconfig['map_zoom'] ) );
 * 			}

 * 			$gm->mScale = ( intval( $mapconfig['map_scale'] ) > 0 ) ? TRUE : FALSE;
 * 			$gm->mMapType = ( intval( $mapconfig['map_type_select']) > 0 ) ? TRUE : FALSE;
 * 			$gm->mContinuousZoom = ( intval( $mapconfig['cont_zoom']) > 0 ) ? TRUE : FALSE;
 * 			$gm->mDoubleClickZoom = ( intval( $mapconfig['dblclick_zoom']) > 0 ) ? TRUE : FALSE;
 * 			$gm->mInset = ( intval( $mapconfig['map_inset'] ) > 0 ) ? TRUE : FALSE;
 * 			$gm->mShowMarker = ( intval( $mapconfig['show_marker'] ) > 0 ) ? TRUE : FALSE;
 * 			$gm->SetMarkerIconStyle( $mapconfig['map_icon_style'] );
 * 			$gm->SetMarkerIconColor( $mapconfig['map_icon_color'] );
 * 			$gm->SetAddress( $address_string );
 * 		}
 * 		return $gm;
 * 	}
 */


	/**
	 * sportsmanagementModelClubInfo::getAddressString()
	 * 
	 * @return
	 */
	function getAddressString( )
	{
		$club = self::getClub();
		if ( !isset ( $club ) ) { return null; }
 		$address_parts = array();
		if (!empty($club->address))
		{
			$address_parts[] = $club->address;
		}
		if (!empty($club->state))
		{
			$address_parts[] = $club->state;
		}
		if (!empty($club->location))
		{
			if (!empty($club->zipcode))
 			{
				$address_parts[] = $club->zipcode. ' ' .$club->location;
			}
			else
			{
				$address_parts[] = $club->location;
			}
		}
		if (!empty($club->country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($club->country);
		}
		$address = implode(', ', $address_parts);
		return $address;
	}

	
	/**
	 * sportsmanagementModelClubInfo::hasEditPermission()
	 * 
	 * @param mixed $task
	 * @return
	 */
	function hasEditPermission($task=null)
	{
		//check for ACL permsission and project admin/editor
		$allowed = parent::hasEditPermission($task);
		$user = JFactory::getUser();
		if ( $user->id > 0 && !$allowed)
		{
			// Check if user is the club admin
			$club = $this->getClub();
			if ( $user->id == $club->admin )
			{
				$allowed = true;
			}
		}
		return $allowed;
	}
    
/**
 *     function checkUserExtraFields()
 *     {
 *         $app = JFactory::getApplication();
 *         //$app->enqueueMessage(JText::_('view -> '.'<pre>'.print_r(JRequest::getVar('view'),true).'</pre>' ),'');
 *     $query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_fields WHERE template_frontend LIKE '".JRequest::getVar('view')."' and published = 1 ";
 * 			//$app->enqueueMessage(JText::_('query -> '.'<pre>'.print_r($query,true).'</pre>' ),'');
 * 			$this->_db->setQuery($query);
 * 			if ($this->_db->loadResult())
 * 			{
 * 			 //$app->enqueueMessage(JText::_('loadResult -> '.'<pre>'.print_r($this->_db->loadResult(),true).'</pre>' ),'');
 * 				return true;
 * 			}
 *             else
 *             {
 *                 return false;
 *             }    
 *         
 *     }
 */
    
/**
 *     function getUserExtraFields($jlid)
 *     {
 *         $app = JFactory::getApplication();
 *     	$query = "SELECT ef.*,
 *         ev.fieldvalue as fvalue,
 *         ev.id as value_id 
 *         FROM #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_fields as ef 
 *         LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."user_extra_fields_values as ev 
 *         ON ef.id = ev.field_id 
 *         AND ev.jl_id = ".$jlid." 
 *         WHERE ef.template_frontend LIKE '".JRequest::getVar('view')."'  
 *         and ef.published = 1
 *         ORDER BY ef.ordering";    
 *         $this->_db->setQuery($query);
 * 		if (!$result=$this->_db->loadObjectList())
 * 		{
 * 			$this->setError($this->_db->getErrorMsg());
 * 			return false;
 * 		}
 *         //$app->enqueueMessage(JText::_('loadResult -> '.'<pre>'.print_r($result,true).'</pre>' ),'');
 * 		return $result;
 *     
 *     }
 */

    
}
?>
