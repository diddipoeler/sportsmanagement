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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');




/**
 * sportsmanagementViewMatches
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewMatches extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewMatches::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model = $this->getModel();
		$params = JComponentHelper::getParams( $option );
        $document = JFactory::getDocument();
        $view = JRequest::getVar( "view") ;
        $_db = JFactory::getDBO(); // the method is contextual so we must have a DBO
        $table_info = $_db->getTableFields('#__sportsmanagement_match');
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($table_info,true).'</pre>'),'Notice');

        $starttime = microtime(); 
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');


        
        $items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $this->project_art_id	= $mainframe->getUserState( "$option.project_art_id", '0' );
        //$this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $this->project_id	= JRequest::getvar('pid', 0);
        if ( !$this->project_id )
        {
            $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        }
        
        $this->rid	= JRequest::getvar('rid', 0);
        if ( !$this->rid )
        {
            $this->rid	= $mainframe->getUserState( "$option.rid", '0' );
        }
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $projectws = $mdlProject->getProject($this->project_id);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' projectws<br><pre>'.print_r($projectws,true).'</pre>'),'');
        }
        
        $mdlRound = JModelLegacy::getInstance("Round", "sportsmanagementModel");
		$roundws = $mdlRound->getRound($this->rid);;
        
        //build the html selectlist for rounds
		$ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true);

		foreach ($ress as $res)
		{
			$datum = sportsmanagementHelper::convertDate($res->round_date_first, 1).' - '.sportsmanagementHelper::convertDate($res->round_date_last, 1);
			$project_roundslist[]=JHtml::_('select.option',$res->id,sprintf("%s (%s)",$res->name,$datum));
		}
		$lists['project_rounds']=JHtml::_(	'select.genericList',$project_roundslist,'rid',
				'class="inputbox" ' .
				'onChange="document.getElementById(\'short_act\').value=\'rounds\';' .
				'document.roundForm.submit();" ',
				'value','text',$roundws->id);

		$lists['project_rounds2']=JHtml::_('select.genericList',$project_roundslist,'rid','class="inputbox" ','value','text',$roundws->id);
        // diddipoeler rounds for change in match
        $project_change_roundslist = array();
        if ( $ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true) )
        {
			$project_change_roundslist = array_merge($project_change_roundslist,$ress);
		}
		$lists['project_change_rounds'] = $project_change_roundslist;
		unset($project_change_roundslist);
        
        //build the html options for teams
		foreach ($items as $row)
		{
			if ( $row->divhomeid == '' )
            {
                $row->divhomeid = 0;
            }
            if ( $row->divawayid == '' )
            {
                $row->divawayid = 0;
            }
            
            if ( $this->project_art_id == 3 )
            {
                $teams[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PERSON'));
            }
            else
            {
                $teams[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
            }
            
			$divhomeid = 0;
			//apply the filter only if both teams are from the same division
			//teams are not from the same division in tournament mode with divisions
			if( $row->divhomeid == $row->divawayid ) {
				$divhomeid = $row->divhomeid;
			} else {
				$row->divhomeid =0;
				$row->divawayid =0;
			}
			if ($projectteams = $mdlProject->getProjectTeamsOptions($this->project_id,$divhomeid)){
				$teams = array_merge($teams,$projectteams);
			}
			$lists['teams_'+$divhomeid] = $teams;
			unset($teams);
		}



        //build the html options for extratime
		$match_result_type[] = JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT'));
		$match_result_type[] = JHtmlSelect::option('1',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT'));
		$match_result_type[] = JHtmlSelect::option('2',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO'));
		$lists['match_result_type'] = $match_result_type;
		unset($match_result_type);
        
        //build the html options for article
        $articles[] = JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ARTICLE'));
        if ($res = sportsmanagementHelper::getArticleList($projectws->category_id)){
			$articles = array_merge($articles,$res);
		}
        $lists['articles'] = $articles;
		unset($articles);
        
        
        //build the html options for divisions
		$divisions[] = JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
		$mdlDivisions = JModelLegacy::getInstance("divisions", "sportsmanagementModel");
		if ($res = $mdlDivisions->getDivisions($this->project_id)){
			$divisions = array_merge($divisions,$res);
		}
		$lists['divisions'] = $divisions;
		unset($divisions);
        
        $document->addScript(JURI::base().'components/'.$option.'/assets/js/matches.js');
        
        foreach ($table_info['#__sportsmanagement_match'] as $field => $value )
        {
        $select_Options = sportsmanagementHelper::getExtraSelectOptions($view,$field); 
        
        if( $select_Options )
        {
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($select_Options,true).'</pre>'),'Notice');  
        
        $select[] = JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));
        $select = array_merge($select,$select_Options);  
        $selectlist[$field] = $select;
		unset($select);
        }   
        
        }


        
		//$this->assignRef('division',$division);

		$this->assign('user',JFactory::getUser());
        $this->assignRef('lists',$lists);
        $this->assignRef('selectlist',$selectlist);
		$this->assignRef('option',$option);
		$this->assignRef('matches',$items);
		$this->assignRef('ress',$ress);
		$this->assignRef('projectws',$projectws);
		$this->assignRef('roundws',$roundws);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
		//$this->assignRef('prefill', $params->get('use_prefilled_match_roster',0));
		
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
  		//// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        // store the variable that we would like to keep for next time
        // function syntax is setUserState( $key, $value );
        $mainframe->setUserState( "$option.rid", $this->rid );
        $mainframe->setUserState( "$option.pid", $this->project_id );
        
        $massadd = JRequest::getInt('massadd',0);

		// Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE');

		if (!$massadd)
		{
			//JToolBarHelper::publishList('matches.publish');
			//JToolBarHelper::unpublishList('matches.unpublish');
            
            JToolBarHelper::publish('match.insertgooglecalendar', 'JLIB_HTML_CALENDAR', true);
            
            JToolBarHelper::publish('matches.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('matches.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();

			JToolBarHelper::apply('matches.saveshort');
			JToolBarHelper::divider();

			JToolBarHelper::custom('match.massadd','new.png','new_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MATCHES'),false);
			JToolBarHelper::addNew('match.addmatch',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH'));
			JToolBarHelper::deleteList(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_WARNING'), 'match.remove');
			JToolBarHelper::divider();

			JToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=rounds');
		}
		else
		{
			JToolBarHelper::custom('match.cancelmassadd','cancel.png','cancel_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'),false);
		}
        
        parent::addToolbar();  
	}
}
?>
