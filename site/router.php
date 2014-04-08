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
* 
* hilfeseite: http://docs.joomla.org/Supporting_SEF_URLs_in_your_component
* 
*/


/**
 * sportsmanagementBuildRoute()
 * 
 * @param mixed $query
 * @return
 */
function sportsmanagementBuildRoute( &$query )
{
    $mainframe = JFactory::getApplication();
    $paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
    $show_debug_info = $paramscomponent->get( 'show_debug_info' );  
    //DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$show_debug_info );
    
    if ( $show_debug_info )
        {
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'   ),'');
    }
    
	$segments = array();

	$view = (isset($query['view']) ? $query['view'] : null);
	if ($view)
	{
		$segments[] = $view;
		unset($query['view']);
	}
	else {
		return $segments;
	}

	// project id, always just after the view if specified
	if (isset($query['p']))
	{
		$segments[] = $query['p'];
		unset( $query['p'] );
	}

	// now, the specifics
	switch ($view)
	{
		case 'predictionrules':
			if (isset($query['prediction_id']))
			{
				$segments[] = $query['prediction_id'];
				unset( $query['prediction_id'] );
			}

			break;
        case 'predictionranking':
			if (isset($query['prediction_id']))
			{
				$segments[] = $query['prediction_id'];
				unset( $query['prediction_id'] );
			}
            if (isset($query['p']))
			{
				$segments[] = $query['p'];
				unset( $query['p'] );
			}
            if (isset($query['pggroup']))
			{
				$segments[] = $query['pggroup'];
				unset( $query['pggroup'] );
			}
            if (isset($query['pj']))
			{
				$segments[] = $query['pj'];
				unset( $query['pj'] );
			}
            if (isset($query['r']))
			{
				$segments[] = $query['r'];
				unset( $query['r'] );
			}
            if (isset($query['pggrouprank']))
			{
				$segments[] = $query['pggrouprank'];
				unset( $query['pggrouprank'] );
			}




            

			break;    
        case 'predictionentry':
			if (isset($query['prediction_id']))
			{
				$segments[] = $query['prediction_id'];
				unset( $query['prediction_id'] );
			}
            if (isset($query['s']))
			{
				$segments[] = $query['s'];
				unset( $query['s'] );
			}
            if (isset($query['pj']))
			{
				$segments[] = $query['pj'];
				unset( $query['pj'] );
			}
            if (isset($query['r']))
			{
				$segments[] = $query['r'];
				unset( $query['r'] );
			}
			break;
        case 'predictionresults':
			if (isset($query['prediction_id']))
			{
				$segments[] = $query['prediction_id'];
				unset( $query['prediction_id'] );
			}
            if (isset($query['p']))
			{
				$segments[] = $query['p'];
				unset( $query['p'] );
			}
            if (isset($query['pggroup']))
			{
				$segments[] = $query['pggroup'];
				unset( $query['pggroup'] );
			}
            if (isset($query['pj']))
			{
				$segments[] = $query['pj'];
				unset( $query['pj'] );
			}
            if (isset($query['r']))
			{
				$segments[] = $query['r'];
				unset( $query['r'] );
			}
            if (isset($query['uid']))
			{
				$segments[] = $query['uid'];
				unset( $query['uid'] );
			}
        break;
        case 'predictionusers':
			if (isset($query['prediction_id']))
			{
				$segments[] = $query['prediction_id'];
				unset( $query['prediction_id'] );
			}
            if (isset($query['uid']))
			{
				$segments[] = $query['uid'];
				unset( $query['uid'] );
			}

			break;
        case 'allprojects':
			if (isset($query['filter_search_nation']))
			{
				$segments[] = $query['filter_search_nation'];
				unset( $query['filter_search_nation'] );
			}
			break;
        
        case 'clubinfo':
			if (isset($query['cid']))
			{
				$segments[] = $query['cid'];
				unset( $query['cid'] );
			}
			break;
		case 'curve':
			if (isset($query['tid1']))
			{
				$segments[] = $query['tid1'];
				unset( $query['tid1'] );
			}
			if (isset($query['tid2']))
			{
				$segments[] = $query['tid2'];
				unset( $query['tid2'] );
			}
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			break;
		case 'eventsranking':
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			if (isset($query['tid']))
			{
				$segments[] = $query['tid'];
				unset( $query['tid'] );
			}
			if (isset($query['evid']))
			{
				$segments[] = $query['evid'];
				unset( $query['evid'] );
			}
			if (isset($query['mid']))
			{
				$segments[] = $query['mid'];
				unset( $query['mid'] );
			}
			break;
		case 'editmatch':
		case 'editevents':
			if (isset($query['mid']))
			{
				$segments[] = $query['mid'];
				unset( $query['mid'] );
			}
			break;
		case "matrix":
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			if (isset($query['r']))
			{
				$segments[] = $query['r'];
				unset( $query['r'] );
			}
			break;
		case 'matchreport':
		case 'nextmatch':
			if (isset($query['mid']))
			{
				$segments[] = $query['mid'];
				unset( $query['mid'] );
			}
			if (isset($query['pics']))
			{
				$segments[] = $query['pics'];
				unset( $query['pics'] );
			}
			if (isset($query['ptid']))
			{
				$segments[] = $query['ptid'];
				unset( $query['ptid'] );
			}
			break;
		case "playground":
			if (isset($query['pgid']))
			{
				$segments[] = $query['pgid'];
				unset( $query['pgid'] );
			}
			break;
		case 'ranking':
			if (isset($query['type']))
			{
				$segments[] = $query['type'];
				unset( $query['type'] );
			}
			if (isset($query['r']))
			{
				$segments[] = $query['r'];
				unset( $query['r'] );
			}
			if (isset($query['from']))
			{
				$segments[] = $query['from'];
				unset( $query['from'] );
			}
			if (isset($query['to']))
			{
				$segments[] = $query['to'];
				unset( $query['to'] );
			}
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			break;
		case 'roster':
		case 'teaminfo':
		case 'teamstats':
			if (isset($query['tid']))
			{
				$segments[] = $query['tid'];
				unset( $query['tid'] );
			}
            // diddipoeler
            if (isset($query['ttid']))
			{
				$segments[] = $query['ttid'];
				unset( $query['ttid'] );
			}
			break;
		case 'teamplan':
			if (isset($query['tid']))
			{
				$segments[] = $query['tid'];
				unset( $query['tid'] );
			}
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			if (isset($query['mode']))
			{
				$segments[] = $query['mode'];
				unset( $query['mode'] );
			}
            // diddipoeler
            if (isset($query['ptid']))
			{
				$segments[] = $query['ptid'];
				unset( $query['ptid'] );
			}
			break;
		case 'results':
		case 'resultsmatrix':
		case 'resultsranking':
			if (isset($query['r']))
			{
				$segments[] = $query['r'];
				unset( $query['r'] );
			}
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			if (isset($query['mode']))
			{
				$segments[] = $query['mode'];
				unset( $query['mode'] );
			}
			if (isset($query['order']))
			{
				$segments[] = $query['order'];
				unset( $query['order'] );
			}
			if (isset($query['form']))
			{
				$segments[] = $query['form'];
				unset( $query['form'] );
			}
			break;
		case 'player':
		case 'staff':
			if (isset($query['tid']))
			{
				$segments[] = $query['tid'];
				unset( $query['tid'] );
			}
			if (isset($query['pid']))
			{
				$segments[] = $query['pid'];
				unset( $query['pid'] );
			}
			break;
		case 'clubs':
		case 'stats':
		case 'teams':
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			break;
		case 'statsranking':
			if (isset($query['division']))
			{
				$segments[] = $query['division'];
				unset( $query['division'] );
			}
			if (isset($query['tid']))
			{
				$segments[] = $query['tid'];
				unset( $query['tid'] );
			}
			break;
		case 'referee':
			if (isset($query['pid']))
			{
				$segments[] = $query['pid'];
				unset( $query['pid'] );
			}
			break;
		case 'tree':
			if (isset($query['did']))
			{
				$segments[] = $query['did'];
				unset( $query['did'] );
			}
			break;
				
		default:
			break;
	}
    
    if ( $show_debug_info )
        {
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' segments<br><pre>'.print_r($segments,true).'</pre>'   ),'');
    }
    
	return $segments;
}

/**
 * sportsmanagementParseRoute()
 * 
 * @param mixed $segments
 * @return
 */
function sportsmanagementParseRoute( $segments )
{
    $mainframe = JFactory::getApplication();
    $paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
    $show_debug_info = $paramscomponent->get( 'show_debug_info' );  
    
    if ( $show_debug_info )
        {
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' segments<br><pre>'.print_r($segments,true).'</pre>'   ),'');
    }
    
	$vars = array();

	$vars['view'] = $segments[0];

	switch( $vars['view'] ) // the view...
	{
		 case 'predictionranking':
			if (isset($segments[1])) 
            {
				$vars['prediction_id'] = $segments[1];
			}
            if (isset($segments[2])) 
            {
				$vars['p'] = $segments[2];
			}
            if (isset($segments[3])) 
            {
				$vars['pggroup'] = $segments[3];
			}
             if (isset($segments[4])) 
            {
				$vars['pj'] = $segments[4];
			}
             if (isset($segments[5])) 
            {
				$vars['r'] = $segments[5];
			}
            if (isset($segments[6])) 
            {
				$vars['pggrouprank'] = $segments[6];
			}


			
			break;
            
        case 'predictionentry':
			if (isset($segments[1])) 
            {
				$vars['prediction_id'] = $segments[1];
			}
            if (isset($segments[2])) 
            {
				$vars['s'] = $segments[2];
			}
             if (isset($segments[3])) 
            {
				$vars['pj'] = $segments[3];
			}
             if (isset($segments[4])) 
            {
				$vars['r'] = $segments[4];
			}
			
			break;
        
        case 'predictionresults':
			if (isset($segments[1])) 
            {
				$vars['prediction_id'] = $segments[1];
			}
            if (isset($segments[2])) 
            {
				$vars['p'] = $segments[2];
			}
            if (isset($segments[3])) 
            {
				$vars['pggroup'] = $segments[3];
			}
             if (isset($segments[4])) 
            {
				$vars['pj'] = $segments[4];
			}
             if (isset($segments[5])) 
            {
				$vars['r'] = $segments[5];
			}
			if (isset($segments[6])) 
            {
				$vars['uid'] = $segments[6];
			}
			break;
        
        case 'predictionusers':
			if (isset($segments[1])) 
            {
				$vars['prediction_id'] = $segments[1];
			}
            if (isset($segments[2])) 
            {
				$vars['uid'] = $segments[2];
			}

			
			break;
        
        case 'predictionrules':
			if (isset($segments[1])) 
            {
				$vars['prediction_id'] = $segments[1];
			}
		
			break;
                    
        case 'allprojects':
			if (isset($segments[1])) 
            {
				$vars['filter_search_nation'] = $segments[1];
			}
			
			break;
            
        case 'clubinfo':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['cid'] = $segments[2];
			}
			break;
		case 'curve':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['tid1'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['tid2'] = $segments[3];
			}
			if (isset($segments[4])) {
				$vars['division'] = $segments[4];
			}
				
			break;
		case 'eventsranking':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['division'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['tid'] = $segments[3];
			}
			if (isset($segments[4])) {
				$vars['evid'] = $segments[4];
			}
			if (isset($segments[5])) {
				$vars['mid'] = $segments[5];
			}
			break;
		case 'editmatch':
		case 'editevents':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['mid'] = $segments[2];
			}
			break;
		case 'matrix':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['division'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['r'] = $segments[3];
			}
			break;
		case 'playground':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['pgid'] = $segments[2];
			}
			break;
		case "ranking":
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['type'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['r'] = $segments[3];
			}
			if (isset($segments[4])) {
				$vars['from'] = $segments[4];
			}
			if (isset($segments[5])) {
				$vars['to'] = $segments[5];
			}
			if (isset($segments[6])) {
				$vars['division'] = $segments[6];
			}
			break;
		case 'teamplan':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['tid'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['division'] = $segments[3];
			}
			if (isset($segments[4])) {
				$vars['mode'] = $segments[4];
			}
            // diddipoeler
            if (isset($segments[5])) {
				$vars['ptid'] = $segments[5];
			}
			break;
		case 'roster':
		case 'teaminfo':
		case 'teamstats':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['tid'] = $segments[2];
			}
            //diddipoeler
            if (isset($segments[3])) {
				$vars['ttid'] = $segments[3];
			}
			break;
				
		case 'results':
		case 'resultsmatrix':
		case 'resultsranking':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['r'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['division'] = $segments[3];
			}
			if (isset($segments[4])) {
				$vars['mode'] = $segments[4];
			}
			if (isset($segments[5])) {
				$vars['order'] = $segments[5];
			}
			if (isset($segments[6])) {
				$vars['layout'] = $segments[6];
			}
			break;
				
		case 'matchreport':
		case 'nextmatch':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['mid'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['pics'] = $segments[3];
			}
			if (isset($segments[4])) {
				$vars['ptid'] = $segments[4];
			}
			break;

			// /esl-dods-fall-league-2010-roster/staff/44-ED - DODS Fall League 2010/1-zj/2-and-one
		case 'player':
		case 'staff':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['tid'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['pid'] = $segments[3];
			}
			break;
		case 'clubs':
		case 'stats':
		case 'teams':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['division'] = $segments[2];
			}
			break;
		case 'statsranking':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['division'] = $segments[2];
			}
			if (isset($segments[3])) {
				$vars['tid'] = $segments[3];
			}
			break;
			// /standard-referee/referee/46/2
		case 'referee':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['pid'] = $segments[2];
			}
			break;
		case 'tree':
			if (isset($segments[1])) {
				$vars['p'] = $segments[1];
			}
			if (isset($segments[2])) {
				$vars['did'] = $segments[2];
			}
			break;
				
		default:
			if (isset($segments[1])) {
			$vars['p'] = $segments[1];
		}
		break;
	}
    
    if ( $show_debug_info )
        {
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' vars<br><pre>'.print_r($vars,true).'</pre>'   ),'');
    }
    
	return $vars;
}
?>