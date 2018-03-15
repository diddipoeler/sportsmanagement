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

jimport( 'joomla.application.component.view' );




/**
 * sportsmanagementViewhitlist
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewhitlist extends JViewLegacy
{
    
	/**
	 * sportsmanagementViewhitlist::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display( $tpl = null )
	{
	   // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $user = JFactory::getUser();
        $model = $this->getModel();
        
        $this->tableclass = $jinput->getVar('table_class', 'table','request','string');
        $this->show_project = $jinput->getVar('show_project', 'table','request','string');
        $this->show_club = $jinput->getVar('show_club', 'table','request','string');
        $this->show_team = $jinput->getVar('show_team', 'table','request','string');
        $this->show_person = $jinput->getVar('show_person', 'table','request','string');
        $this->show_playground = $jinput->getVar('show_playground', 'table','request','string');
        $this->max_hits = $jinput->getVar('max_hits', 'table','request','string');
        
        if ( $this->show_project )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'project');
        }
        if ( $this->show_club )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'club');
        }
        if ( $this->show_team )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'team');
        }
        if ( $this->show_person )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'person');
        }
        
        if ( $this->show_playground )
        {
            $items = $model->getSportsmanagementHits(NULL,$this->max_hits,'playground');
        }
        
        //$teams = $model->getTeamHits($config);
        
        $this->model_hits  = $model::$_success_text;
	
		parent::display( $tpl );
	}

}
?>