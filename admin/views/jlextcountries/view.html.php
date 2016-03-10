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
 * sportsmanagementViewjlextcountries
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextcountries extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewjlextcountries::init()
     * 
     * @return void
     */
    public function init ()
	{
		
        $inputappend = '';
        
        //$app->enqueueMessage(sprintf(JText::_('COM_SPORTSMANAGEMENT_JOOMLA_VERSION'), COM_SPORTSMANAGEMENT_JOOMLAVERSION),'');
        //$app->enqueueMessage(JText::_('Layout -> ').$this->getLayout(),'');
        
        
        $starttime = microtime(); 
		
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		
        
        $this->table = JTable::getInstance('jlextcountry', 'sportsmanagementTable');

        
         //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_FEDERATION'));
		if ($res = $this->get('Federation') )
        {
            $nation = array_merge($nation,$res);
            $this->federation	= $res;
        

        }
		
        $lists['federation'] = JHtmlSelect::genericlist(	$nation,
																'filter_federation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.federation'));
                                                                

	
		$this->lists	= $lists;
	



	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	
       
        // Set toolbar items for the page
		JToolBarHelper::addNew('jlextcountry.add');
		JToolBarHelper::editList('jlextcountry.edit');
		JToolBarHelper::custom('jlextcountry.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
        JToolBarHelper::custom('jlextcountries.importplz','upload','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_IMPORT_PLZ'),true);
		JToolBarHelper::archiveList('jlextcountry.export',JText::_('JTOOLBAR_EXPORT'));
        JToolbarHelper::checkin('jlextcountries.checkin');
		//JToolBarHelper::deleteList('', 'jlextcountries.delete', 'JTOOLBAR_DELETE');
        if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
        {
		JToolbarHelper::trash('jlextcountries.trash');
        }
        else
        {
        JToolBarHelper::deleteList('', 'jlextcountries.delete', 'JTOOLBAR_DELETE');    
        }
	
        
        parent::addToolbar();
	}
}
?>