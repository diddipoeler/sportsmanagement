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
 * sportsmanagementViewrosterpositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewrosterpositions extends sportsmanagementView
{
	/**
	 * sportsmanagementViewrosterpositions::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		
        
        $this->table = JTable::getInstance('rosterposition', 'sportsmanagementTable');
		
		
		
	}
    
    	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	
// Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_TITLE');
        JToolbarHelper::custom('rosterpositions.addhome', 'new', 'new', JText::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_HOME'), false);
		JToolbarHelper::custom('rosterpositions.addaway', 'new', 'new', JText::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_AWAY'), false);
		JToolbarHelper::editList('rosterposition.edit');
		//JToolbarHelper::custom('rosterposition.import','upload','upload',JText::_('COM_SPORTSMAMAGEMENT_GLOBAL_CSV_IMPORT'),false);
		//JToolbarHelper::archiveList('rosterposition.export',JText::_('COM_SPORTSMAMAGEMENT_GLOBAL_XML_EXPORT'));
		
		//JToolbarHelper::deleteList('', 'rosterpositions.delete');
		if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
		{
			JToolbarHelper::trash('rosterpositions.trash');
		}
		else
		{
			JToolbarHelper::deleteList('', 'rosterpositions.delete', 'JTOOLBAR_DELETE');
		}
		
		JToolbarHelper::checkin('rosterpositions.checkin');
		parent::addToolbar();  
       
       
       
    }   

}
?>
