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
defined('_JEXEC') or die('Restricted access');




/**
 * sportsmanagementViewPositions
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewPositions extends sportsmanagementView
{
	/**
	 * sportsmanagementViewPositions::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
			

$starttime = microtime(); 
		//$items = $this->get('Items');
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		
        
		$this->table = JTable::getInstance('position', 'sportsmanagementTable');



		//build the html options for parent position
		$parent_id[] = JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));
		if ($res = $this->model->getParentsPositions())
		{
			foreach ($res as $re){$re->text = JText::_($re->text);}
			$parent_id = array_merge($parent_id, $res);
		}
		$lists['parent_id'] = $parent_id;
        //$lists['parents']=$parent_id;
		unset($parent_id);

		//build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_SPORTSTYPE_FILTER'), 'id', 'name');
		//$allSportstypes =& sportsmanagementModelSportsTypes::getSportsTypes();
		$allSportstypes = JModelLegacy::getInstance('SportsTypes','sportsmanagementmodel')->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);
        
        $this->sports_type	= $allSportstypes;
        
		$lists['sportstypes'] = JHtml::_( 'select.genericList', 
										$sportstypes, 
										'filter_sports_type', 
										'class="inputbox" onChange="this.form.submit();" style="width:120px"', 
										'id', 
										'name', 
										$this->state->get('filter.sports_type'));
		unset($sportstypes);
		
		$this->lists = $lists;

		
        
        
		
	}
	
	
	/**
	 * sportsmanagementViewPositions::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{

		// Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TITLE');

		JToolBarHelper::publish('positions.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('positions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();

		JToolBarHelper::apply('positions.saveshort');
		JToolBarHelper::editList('position.edit');
		JToolBarHelper::addNew('position.add');
		JToolBarHelper::custom('position.import', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
		JToolBarHelper::archiveList('position.export', JText::_('JTOOLBAR_EXPORT'));
        JToolbarHelper::checkin('positions.checkin');
		//JToolBarHelper::deleteList('','position.delete');
        if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
        {
		JToolbarHelper::trash('positions.trash');
        }
        else
        {
        JToolBarHelper::deleteList('', 'positions.delete', 'JTOOLBAR_DELETE');    
        }

		
        parent::addToolbar();
	}
}
?>
