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
 * sportsmanagementViewUpdates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewUpdates extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewUpdates::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$mainframe->setUserState($option.'update_part',0); // 0
		$filter_order		= $mainframe->getUserStateFromRequest($option.'updates_filter_order','filter_order','dates','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'updates_filter_order_Dir','filter_order_Dir','','word');
		
		
        
		$db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$model = $this->getModel();
		$versions=$model->getVersions();
		//$versionhistory=$model->getVersionHistory();
		$updateFiles = array();
		$lists=array();
		$updateFiles=$model->loadUpdateFiles();
        /*
        if($updateFiles=$model->loadUpdateFiles()) {
			for ($i=0, $n=count($updateFiles); $i < $n; $i++)
			{
				foreach ($versions as $version)
				{
					if (strpos($version->version,$updateFiles[$i]['file_name']))
					{
						$updateFiles[$i]['updateTime']=$version->date;
						break;
					}
					else
					{
						$updateFiles[$i]['updateTime']="-";
					}
				}
			}
		}
        */
		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;
		//$this->assignRef('versionhistory',$versionhistory);
		$this->assignRef('updateFiles',$updateFiles);
		$this->assign('request_url',$uri->toString());
		$this->assignRef('lists',$lists);
        $this->assignRef('option',$option);
        
       
        
//        $this->addToolbar();
//		parent::display($tpl);
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	//// Get a refrence of the page instance in joomla
//        $document = JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_TITLE');
        $this->icon = 'updates';
//		sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
        parent::addToolbar();

        
    }    
    
    
    
}
?>
