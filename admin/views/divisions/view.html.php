<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		administrator/components/sportsmanagement/views/divisions/view.html.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewDivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewDivisions extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewDivisions::init()
	 * 
	 * @return void
	 */
	public function init ()
	{

        $lists = array();
        $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        $starttime = microtime(); 
    
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }

        $this->table = JTable::getInstance('division', 'sportsmanagementTable');
        $this->projectws = $project;
		$this->lists = $lists;

	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
        // Set toolbar items for the page
		$this->title =  JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE' );
        
        JToolBarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::checkin('divisions.checkin');
        JToolBarHelper::apply('divisions.saveshort');
		JToolBarHelper::divider();
		JToolBarHelper::addNew('division.add');
		JToolBarHelper::editList('division.edit');

        parent::addToolbar();
	}
}
?>