<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

jimport( 'joomla.application.component.view');

/**
 * sportsmanagementViewMatrix
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewMatrix extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewMatrix::init()
	 * 
	 * @return void
	 */
	function init()
	{

		$this->divisionid = sportsmanagementModelMatrix::$divisionid;
		$this->roundid = sportsmanagementModelMatrix::$roundid;
		$this->division = $this->model->getDivision();
		$this->round = $this->model->getRound();
        
        if ( isset($this->config['teamnames']) && $this->config['teamnames'] )
        {
		$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid( sportsmanagementModelMatrix::$divisionid,$this->config['teamnames'],$this->jinput->getInt('cfg_which_database',0) );
        }
        else
        {
        $this->config['teamnames'] = 'name';    
        $this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid( sportsmanagementModelMatrix::$divisionid,'name',$this->jinput->getInt('cfg_which_database',0) );    
        }
        
        if ( !isset($this->config['image_placeholder']) )
        {
		$this->config['image_placeholder'] = '';
        }
        
		$this->results = $this->model->getMatrixResults( $this->project->id );
	
    if ( isset($this->config['show_matrix_russia']) )
    {	
        if (($this->config['show_matrix_russia'])==1)
	{
	$this->russiamatrix = $this->model->getRussiaMatrixResults($this->teams, $this->results );
    } 
    }
		if ($this->project->project_type == 'DIVISIONS_LEAGUE' && !$this->divisionid )
		{
			$ranking_reason = array();
      $divisions = sportsmanagementModelProject::getDivisions(0,$this->jinput->getInt('cfg_which_database',0));
			$this->divisions = $divisions;
			
			foreach ( $this->results as $result ) 
      {
      foreach ( $this->teams as $teams ) 
        {
        
        if ( $result->division_id )
        {

        if ( ($result->projectteam1_id == $teams->projectteamid) || ($result->projectteam2_id == $teams->projectteamid) ) 
        {
        $teams->division_id = $result->division_id;
        
        if ( $teams->start_points )
        {
        
        if ( $teams->start_points < 0 )
        {
        $color = "red";
        }
        else
        {
        $color = "green";
        }
        
        $ranking_reason[$result->division_id][$teams->name] = '<font color="'.$color.'">'.$teams->name.': '.$teams->start_points.' Punkte Grund: '.$teams->reason.'</font>';
        }
        
        }

        }
        
        }
  
      }
      
    foreach ( $this->divisions as $row )
		{
		if ( isset($ranking_reason[$row->id]) )
		{
    $row->notes = implode(", ",$ranking_reason[$row->id]);
    }
    
    }
    
		}
		
		if(!is_null($this->project)) 
        {
			$this->favteams = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database',0));
		}
		
    // Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE' );
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$this->document->setTitle( $pageTitle );
		//$view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'results<pre>'.print_r($this->results,true).'</pre>';
        $my_text .= 'teams<pre>'.print_r($this->teams,true).'</pre>';
        $my_text .= 'config<pre>'.print_r($this->config,true).'</pre>';    
        $my_text .= 'project<pre>'.print_r($this->project,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        
	}
}
?>