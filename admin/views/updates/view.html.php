<?php

/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version         1.0.05
 * @file                agegroup.php
 * @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
 * SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
 * ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
 * OHNE JEDE GEW�HRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License f�r weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html.bootstrap');
jimport('joomla.application.component.view');

/**
 * sportsmanagementViewUpdates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewUpdates extends sportsmanagementView {

    /**
     * sportsmanagementViewUpdates::init()
     * 
     * @return void
     */
    public function init() {
        //	$app = JFactory::getApplication();
//		$jinput = $app->input;
        //	$option = $jinput->getCmd('option');
        $this->app->setUserState($this->option . 'update_part', 0); // 0
        $filter_order = $this->app->getUserStateFromRequest($this->option . 'updates_filter_order', 'filter_order', 'dates', 'cmd');
        $filter_order_Dir = $this->app->getUserStateFromRequest($this->option . 'updates_filter_order_Dir', 'filter_order_Dir', '', 'word');



        $db = sportsmanagementHelper::getDBConnection();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $model = $this->getModel();
        $versions = $model->getVersions();
        //$versionhistory=$model->getVersionHistory();
        $updateFiles = array();
        $lists = array();
        $updateFiles = $model->loadUpdateFiles();
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
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        //$this->assignRef('versionhistory',$versionhistory);
        $this->updateFiles = $updateFiles;
        $this->request_url = $uri->toString();
        $this->lists = $lists;




//        $this->addToolbar();
//		parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.7
     */
    protected function addToolbar() {
        //// Get a refrence of the page instance in joomla
//        $document = JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_TITLE');
        $this->icon = 'updates';
//		sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolbarHelper::preferences(JFactory::getApplication()->input->getCmd('option'));

        parent::addToolbar();
    }

}

?>
