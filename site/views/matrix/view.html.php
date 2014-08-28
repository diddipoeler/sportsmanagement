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
class sportsmanagementViewMatrix extends JViewLegacy
{
	/**
	 * sportsmanagementViewMatrix::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document= JFactory::getDocument();
        $option = JRequest::getCmd('option');

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$project = sportsmanagementModelProject::getProject();
		
		$this->assignRef('model', $model);
		$this->assignRef('project', $project);
		$this->assign('overallconfig', sportsmanagementModelProject::getOverallConfig() );

		$this->assignRef('config', $config );

		$this->assign('divisionid', $model->getDivisionID() );
		$this->assign('roundid', $model->getRoundID() );
		$this->assign('division', $model->getDivision() );
		$this->assign('round', $model->getRound() );
		$this->assign('teams', sportsmanagementModelProject::getTeamsIndexedByPtid( $model->getDivisionID(),$this->config->teamnames ) );
		$this->assign('results', $model->getMatrixResults( $project->id ) );
		
        if (($this->config['show_matrix_russia'])==1)
	{
	$this->assign('russiamatrix', $model->getRussiaMatrixResults($this->teams, $this->results ) );
    } 
    
		if ($project->project_type == 'DIVISIONS_LEAGUE' && !$this->divisionid )
		{
			$ranking_reason = array();
      $divisions = $model->getDivisions();
			$this->assignRef('divisions', $divisions);
			
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
		
		if(!is_null($project)) {
			$this->assign('favteams', sportsmanagementModelProject::getFavTeams() );
		}
    
    //$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
		
    // Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE' );
		if ( isset( $project->name ) )
		{
			$pageTitle .= ': ' . $project->name;
		}
		$document->setTitle( $pageTitle );
		$view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		parent::display( $tpl );
	}
}
?>