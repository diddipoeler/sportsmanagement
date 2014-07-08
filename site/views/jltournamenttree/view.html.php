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


jimport( 'joomla.application.component.view');

/**
 * sportsmanagementViewjltournamenttree
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjltournamenttree extends JViewLegacy
{
	/**
	 * sportsmanagementViewjltournamenttree::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display( $tpl = null )
	{
	$option = JRequest::getCmd('option');	
    // Get a refrence of the page instance in joomla
	$document = JFactory::getDocument();
	$uri = JFactory::getURI();		
	$mainframe = JFactory::getApplication();		
//	$js ="registerhome('".JURI::base()."','Tournament Tree Extension','".$mainframe->getCfg('sitename')."','0');". "\n";
//  $document->addScriptDeclaration( $js );	
    
    //$model =& $this->getModel( 'jlxmlexports' ); 
    $model = $this->getModel();
    //$model->checkStartExtension();
    $bracket_request = JRequest::get();
    $this->assignRef( 'logo', $bracket_request['tree_logo'] );
    
    $this->assign( 'color_from', $model->getColorFrom() );
    $this->assign( 'color_to', $model->getColorTo() );
    $this->assign( 'font_size', $model->getFontSize() );
    
    if ( !$this->font_size )
    {
        $this->font_size = '14';
    }
    
    if ( !$this->color_from )
    {
        $this->color_from = '#FFFFFF';
    }
    if ( !$this->color_to )
    {
        $this->color_to = '#0000FF';
    }
    
    
    $this->assign( 'rounds',		$model->getTournamentRounds() );
    $this->assign( 'projectname',		$model->getTournamentName() );
    
    $this->assign( 'bracket_rounds',		$model->getTournamentBracketRounds($this->rounds) );
    $this->assign( 'bracket_teams',		$model->getTournamentMatches($this->rounds) );
    $this->assign( 'bracket_results',		$model->getTournamentResults($this->rounds) );
    
    $this->assign( 'which_first_round',		$model->getWhichShowFirstRound() );
    $this->assign( 'jl_tree_bracket_round_width', $model->getTreeBracketRoundWidth() );	
    $this->assign( 'jl_tree_bracket_teamb_width', $model->getTreeBracketTeambWidth() );
    $this->assign( 'jl_tree_bracket_width', $model->getTreeBracketWidth() );
   
   //$this->assign( 'jl_tree_jquery_version',		$model->getWhichJQuery() );
   
// Add Script
//$document->addScript(JURI::base().'components/com_sportsmanagement/extensions/jltournamenttree/assets/js/jquery-1.7.2.min.js');

//$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/'.$this->jl_tree_jquery_version.'/jquery.min.js');
//$document->addScript('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js');

//$document->addScript(JURI::base().'components/com_sportsmanagement/extensions/jltournamenttree/assets/js/jquery-ui-1.8.21.custom.min.js');
$document->addScript(JURI::base().'components/'.$option.'/assets/js/jquery.json-2.3.min.js');
$document->addScript(JURI::base().'components/'.$option.'/assets/js/jquery.bracket-3.js');

// Add customstyles
$stylelink = '<link rel="stylesheet" href="'.JURI::base().'components/'.$option.'/assets/css/jquery.bracket-3.css'.'" type="text/css" />' ."\n";
$document->addCustomTag($stylelink);
//$stylelink = '<link rel="stylesheet" href="'.JURI::base().'components/com_sportsmanagement/extensions/jltournamenttree/assets/css/jquery-ui-1.8.16.custom.css'.'" type="text/css" />' ."\n";
//$document->addCustomTag($stylelink);
$stylelink = '<link rel="stylesheet" href="'.JURI::base().'components/'.$option.'/assets/css/jquery.bracket-site.css'.'" type="text/css" />' ."\n";
$document->addCustomTag($stylelink);


/*
$style = 'div.jQBracket {'
. '  font-family: "Arial";'
. '  font-size: 14px;'
. '  float: left;'
. '  clear: both;'
. '  position: relative;'
. '  background-color: #333333;'
. '  background: -webkit-gradient(linear, 0% 0%, 0% 100%, from('.$bracket_request['color_from'].'), to('.$bracket_request['color_to'].'));'
. '  background: -moz-linear-gradient(-90deg, '.$bracket_request['color_from'].', '.$bracket_request['color_to'].');' 
. '  }';
      
$document->addStyleDeclaration( $style );
*/



		parent::display( $tpl );
	}
}
?>