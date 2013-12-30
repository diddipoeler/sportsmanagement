<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                jlextdfbnetplayerimport.php
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
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.application.component.view' );



class sportsmanagementViewjlextsisimport extends JView 
{
	function display($tpl = null) 
    {
		//global $mainframe;
		
		if ($this->getLayout () == 'default') {
			$this->_displayDefault ( $tpl );
			return;
		}
		
        /*
		if ($this->getLayout () == 'default_edit') {
			$this->_displayDefaultEdit ( $tpl );
			return;
		}
		
		if ($this->getLayout () == 'default_update') {
			$this->_displayDefaultUpdate ( $tpl );
			return;
		}
		
		if ($this->getLayout () == 'info') {
			$this->_displayInfo ( $tpl );
			return;
		}
		
		if ($this->getLayout () == 'selectpage') {
			$this->_displaySelectpage ( $tpl );
			return;
		}
		*/
        
		// Set toolbar items for the page
		//JToolBarHelper::title ( JText::_ ( 'COM_JOOMLEAGUE_ADMIN_LMO_IMPORT_TITLE_1_3' ), 'generic.png' );
		//JToolBarHelper::help ( 'screen.joomleague', true );
		
		$uri = JFactory::getURI ();
		$config = JComponentHelper::getParams ( 'com_media' );
		$post = JRequest::get ( 'post' );
		$files = JRequest::get ( 'files' );
		
		$this->assignRef ( 'request_url', $uri->toString () );
		$this->assignRef ( 'config', $config );
		
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		
		parent::display ( $tpl );
	}
	
    /*
    private function _displayInfo($tpl) {
		$mtime = microtime ();
		$mtime = explode ( " ", $mtime );
		$mtime = $mtime [1] + $mtime [0];
		$starttime = $mtime;
		$mainframe = JFactory::getApplication ();
		$db = JFactory::getDBO ();
		$post = JRequest::get ( 'post' );
		
		$model = $this->getModel ( 'jlextdfbnetplayerimport' );
		
		// Set toolbar items for the page
		// JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_TITLE_3_3'),'generic.png');
		// JToolBarHelper::back();
		// JToolBarHelper::help('screen.joomleague',true);
		
		$this->assignRef ( 'starttime', $starttime );
		$this->assignRef ( 'importData', $model->importData ( $post ) );
		$this->assignRef ( 'postData', $post );
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		
		
		parent::display ( $tpl );
	}
    */
    
	function _displayDefault($tpl) 
    {
		//global $option;
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication ();
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		
		// $model = $this->getModel('project') ;
		// $projectdata = $this->get('Data');
		// $this->assignRef( 'name', $projectdata->name);
		
		$model = $this->getModel ();
		$project = $mainframe->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
        $params = JComponentHelper::getParams( $option );
        $sis_xmllink	= $params->get( 'sis_xmllink' );
        $sis_nummer	= $params->get( 'sis_meinevereinsnummer' );
        $sis_passwort	= $params->get( 'sis_meinvereinspasswort' );
		
//        $mainframe->enqueueMessage(JText::_('sis_xmllink<br><pre>'.print_r($sis_xmllink,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sis_meinevereinsnummer<br><pre>'.print_r($sis_nummer,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sis_meinvereinspasswort<br><pre>'.print_r($sis_passwort,true).'</pre>'   ),'');
        
		$this->assign ( 'request_url', $uri->toString () );
		$this->assignRef ( 'config', $config );
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		$import_version = 'NEW';
		$this->assignRef ( 'import_version', $import_version );
		
		$this->addToolbar ();
		parent::display ( $tpl );
	}
    
    
	function _displayDefaultUpdate($tpl) 
    {
		// global $mainframe, $option;
		$mainframe = & JFactory::getApplication ();
		$option = JRequest::getCmd ( 'option' );
		
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		$model = $this->getModel ();
		//$option = 'com_joomleague';
		$project = $mainframe->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
		
		$uploadArray = $mainframe->getUserState ( $option . 'uploadArray', array () );
		$lmoimportuseteams = $mainframe->getUserState ( $option . 'lmoimportuseteams' );
		$whichfile = $mainframe->getUserState ( $option . 'whichfile' );
		//$delimiter = $mainframe->getUserState ( $option . 'delimiter' );
		
		$this->assignRef ( 'uploadArray', $uploadArray );
		
		$this->assignRef ( 'importData', $model->getUpdateData () );
		
		// $this->assignRef('xml',$model->getData());
		
		parent::display ( $tpl );
	}
    
    /*
	function _displayDefaultEdit($tpl) {
		// global $mainframe, $option;
		$mainframe = & JFactory::getApplication ();
		$option = JRequest::getCmd ( 'option' );
		
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$user = JFactory::getUser ();
		$model = $this->getModel ();
		// $option='com_joomleague';
		$project = $mainframe->getUserState ( $option . 'project' );
		$this->assignRef ( 'project', $project );
		$config = JComponentHelper::getParams ( 'com_media' );
		
		$uploadArray = $mainframe->getUserState ( $option . 'uploadArray', array () );
		$lmoimportuseteams = $mainframe->getUserState ( $option . 'lmoimportuseteams' );
		$whichfile = $mainframe->getUserState ( $option . 'whichfile' );
		//$delimiter = $mainframe->getUserState ( $option . 'delimiter' );
		
		$countries = new Countries ();
		$this->assignRef ( 'uploadArray', $uploadArray );
		
		$this->assignRef ( 'countries', $countries->getCountries () );
		$this->assignRef ( 'request_url', $uri->toString () );
		
		$this->assignRef ( 'xml', $model->getData () );
		$this->assignRef ( 'leagues', $model->getLeagueList () );
		$this->assignRef ( 'seasons', $model->getSeasonList () );
		$this->assignRef ( 'sportstypes', $model->getSportsTypeList () );
		$this->assignRef ( 'admins', $model->getUserList ( true ) );
		$this->assignRef ( 'editors', $model->getUserList ( false ) );
		$this->assignRef ( 'templates', $model->getTemplateList () );
		$this->assignRef ( 'teams', $model->getTeamList () );
		$this->assignRef ( 'clubs', $model->getClubList () );
		$this->assignRef ( 'persons', $model->getPersonList () );
		$this->assignRef ( 'positions', $model->getPositionList () );
		$this->assignRef ( 'parentpositions', $model->getParentPositionList () );
		
		if ($whichfile == 'playerfile') {
			$this->xml ['project']->name = '';
			$this->xml ['team'] = '';
			$this->style = ' style="visibility: hidden;" ';
			$this->insert_task = 'insertplayer';
		} else {
			$this->style = '';
			$this->insert_task = 'insertmatch';
		}
		
		$this->assignRef ( 'playgrounds', $model->getPlaygroundList () );
		$this->assignRef ( 'projects', $model->getProjectList () );
		// }
		
		$this->assignRef ( 'config', $config );
		
		// LOCALE SETTINGS
		$timezones = array (
				JHtml::_ ( 'select.option', - 12, JText::_ ( '(UTC -12:00) International Date Line West' ) ),
				JHtml::_ ( 'select.option', - 11, JText::_ ( '(UTC -11:00) Midway Island, Samoa' ) ),
				JHtml::_ ( 'select.option', - 10, JText::_ ( '(UTC -10:00) Hawaii' ) ),
				JHtml::_ ( 'select.option', - 9.5, JText::_ ( '(UTC -09:30) Taiohae, Marquesas Islands' ) ),
				JHtml::_ ( 'select.option', - 9, JText::_ ( '(UTC -09:00) Alaska' ) ),
				JHtml::_ ( 'select.option', - 8, JText::_ ( '(UTC -08:00) Pacific Time (US &amp; Canada)' ) ),
				JHtml::_ ( 'select.option', - 7, JText::_ ( '(UTC -07:00) Mountain Time (US &amp; Canada)' ) ),
				JHtml::_ ( 'select.option', - 6, JText::_ ( '(UTC -06:00) Central Time (US &amp; Canada), Mexico City' ) ),
				JHtml::_ ( 'select.option', - 5, JText::_ ( '(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima' ) ),
				JHtml::_ ( 'select.option', - 4, JText::_ ( '(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz' ) ),
				JHtml::_ ( 'select.option', - 4.5, JText::_ ( '(UTC -04:30) Venezuela' ) ),
				JHtml::_ ( 'select.option', - 3.5, JText::_ ( '(UTC -03:30) St. John\'s, Newfoundland, Labrador' ) ),
				JHtml::_ ( 'select.option', - 3, JText::_ ( '(UTC -03:00) Brazil, Buenos Aires, Georgetown' ) ),
				JHtml::_ ( 'select.option', - 2, JText::_ ( '(UTC -02:00) Mid-Atlantic' ) ),
				JHtml::_ ( 'select.option', - 1, JText::_ ( '(UTC -01:00) Azores, Cape Verde Islands' ) ),
				JHtml::_ ( 'select.option', 0, JText::_ ( '(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca' ) ),
				JHtml::_ ( 'select.option', 1, JText::_ ( '(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris' ) ),
				JHtml::_ ( 'select.option', 2, JText::_ ( '(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa' ) ),
				JHtml::_ ( 'select.option', 3, JText::_ ( '(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg' ) ),
				JHtml::_ ( 'select.option', 3.5, JText::_ ( '(UTC +03:30) Tehran' ) ),
				JHtml::_ ( 'select.option', 4, JText::_ ( '(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi' ) ),
				JHtml::_ ( 'select.option', 4.5, JText::_ ( '(UTC +04:30) Kabul' ) ),
				JHtml::_ ( 'select.option', 5, JText::_ ( '(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent' ) ),
				JHtml::_ ( 'select.option', 5.5, JText::_ ( '(UTC +05:30) Bombay, Calcutta, Madras, New Delhi, Colombo' ) ),
				JHtml::_ ( 'select.option', 5.75, JText::_ ( '(UTC +05:45) Kathmandu' ) ),
				JHtml::_ ( 'select.option', 6, JText::_ ( '(UTC +06:00) Almaty, Dhaka' ) ),
				JHtml::_ ( 'select.option', 6.5, JText::_ ( '(UTC +06:30) Yagoon' ) ),
				JHtml::_ ( 'select.option', 7, JText::_ ( '(UTC +07:00) Bangkok, Hanoi, Jakarta' ) ),
				JHtml::_ ( 'select.option', 8, JText::_ ( '(UTC +08:00) Beijing, Perth, Singapore, Hong Kong' ) ),
				JHtml::_ ( 'select.option', 8.75, JText::_ ( '(UTC +08:00) Ulaanbaatar, Western Australia' ) ),
				JHtml::_ ( 'select.option', 9, JText::_ ( '(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk' ) ),
				JHtml::_ ( 'select.option', 9.5, JText::_ ( '(UTC +09:30) Adelaide, Darwin, Yakutsk' ) ),
				JHtml::_ ( 'select.option', 10, JText::_ ( '(UTC +10:00) Eastern Australia, Guam, Vladivostok' ) ),
				JHtml::_ ( 'select.option', 10.5, JText::_ ( '(UTC +10:30) Lord Howe Island (Australia)' ) ),
				JHtml::_ ( 'select.option', 11, JText::_ ( '(UTC +11:00) Magadan, Solomon Islands, New Caledonia' ) ),
				JHtml::_ ( 'select.option', 11.5, JText::_ ( '(UTC +11:30) Norfolk Island' ) ),
				JHtml::_ ( 'select.option', 12, JText::_ ( '(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka' ) ),
				JHtml::_ ( 'select.option', 12.75, JText::_ ( '(UTC +12:45) Chatham Island' ) ),
				JHtml::_ ( 'select.option', 13, JText::_ ( '(UTC +13:00) Tonga' ) ),
				JHtml::_ ( 'select.option', 14, JText::_ ( '(UTC +14:00) Kiribati' ) ) 
		);
		
		// JToolBarHelper::custom($this->insert_task,'upload','upload',Jtext::_('COM_JOOMLEAGUE_ADMIN_XML_IMPORT_START_BUTTON'), false);
		JLToolBarHelper::custom ( $this->insert_task, 'upload', 'upload', Jtext::_ ( 'COM_JOOMLEAGUE_ADMIN_XML_IMPORT_START_BUTTON' ), false );
		
		$conf = JFactory::getConfig ();
		$value = $conf->getValue ( 'config.offset' );
		
		$lists ['serveroffset'] = JHtml::_ ( 'select.genericlist', $timezones, 'serveroffset', ' class="inputbox"', 'value', 'text', $value );
		$this->assignRef ( 'lists', $lists );
		
		$revisionDate = '2011-04-28 - 12:00';
		$import_version = 'NEW';
		$this->assignRef ( 'revisionDate', $revisionDate );
		$this->assignRef ( 'import_version', $import_version );
		
		parent::display ( $tpl );
	}
    */
    
    /*
	private function _displaySelectpage($tpl) {
		// $option='com_joomleague';
		$mainframe = JFactory::getApplication ();
		$option = JRequest::getCmd ( 'option' );
		$document = JFactory::getDocument ();
		$document->addScript ( '/administrator/components/com_joomleague/assets/js/COM_JOOMLEAGUE_import.js' );
		$db = JFactory::getDBO ();
		$uri = JFactory::getURI ();
		$model = $this->getModel ( 'jlextdfbnetplayerimport' );
		
		$revisionDate = '2011-04-28 - 12:00';
		$this->assignRef ( 'revisionDate', $revisionDate );
		
		$lists = array ();
		
		$this->assignRef ( 'request_url', $uri->toString () );
		$this->assignRef ( 'selectType', $mainframe->getUserState ( $option . 'selectType' ) );
		$this->assignRef ( 'recordID', $mainframe->getUserState ( $option . 'recordID' ) );
		
		switch ($this->selectType) {
			case '8' :
				{ // Select Statistics
					$this->assignRef ( 'statistics', $model->getStatisticsListSelect () );
					$statisticlist = array ();
					$statisticlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_STATISTIC' ) );
					$statisticlist = array_merge ( $statisticlist, $this->statistics );
					$lists ['statistics'] = JHtml::_ ( 'select.genericlist', $statisticlist, 'statisticID', 'class="inputbox select-statistic" onchange="javascript:insertStatistic()" ' );
					unset ( $statisticlist );
				}
				break;
			
			case '7' :
				{ // Select ParentPosition
					$this->assignRef ( 'parentpositions', $model->getParentPositionListSelect () );
					$parentpositionlist = array ();
					$parentpositionlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_PARENT_POSITION' ) );
					$parentpositionlist = array_merge ( $parentpositionlist, $this->parentpositions );
					$lists ['parentpositions'] = JHtml::_ ( 'select.genericlist', $parentpositionlist, 'parentPositionID', 'class="inputbox select-parentposition" onchange="javascript:insertParentPosition()" ' );
					unset ( $parentpositionlist );
				}
				break;
			
			case '6' :
				{ // Select Position
					$this->assignRef ( 'positions', $model->getPositionListSelect () );
					$positionlist = array ();
					$positionlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_POSITION' ) );
					$positionlist = array_merge ( $positionlist, $this->positions );
					$lists ['positions'] = JHtml::_ ( 'select.genericlist', $positionlist, 'positionID', 'class="inputbox select-position" onchange="javascript:insertPosition()" ' );
					unset ( $positionlist );
				}
				break;
			
			case '5' :
				{ // Select Event
					$this->assignRef ( 'events', $model->getEventListSelect () );
					$eventlist = array ();
					$eventlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_EVENT' ) );
					$eventlist = array_merge ( $eventlist, $this->events );
					$lists ['events'] = JHtml::_ ( 'select.genericlist', $eventlist, 'eventID', 'class="inputbox select-event" onchange="javascript:insertEvent()" ' );
					unset ( $eventlist );
				}
				break;
			
			case '4' :
				{ // Select Playground
					$this->assignRef ( 'playgrounds', $model->getPlaygroundListSelect () );
					$playgroundlist = array ();
					$playgroundlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_PLAYGROUND' ) );
					$playgroundlist = array_merge ( $playgroundlist, $this->playgrounds );
					$lists ['playgrounds'] = JHtml::_ ( 'select.genericlist', $playgroundlist, 'playgroundID', 'class="inputbox select-playground" onchange="javascript:insertPlayground()" ' );
					unset ( $playgroundlist );
				}
				break;
			
			case '3' :
				{ // Select Person
					$this->assignRef ( 'persons', $model->getPersonListSelect () );
					$personlist = array ();
					$personlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_PERSON' ) );
					$personlist = array_merge ( $personlist, $this->persons );
					$lists ['persons'] = JHtml::_ ( 'select.genericlist', $personlist, 'personID', 'class="inputbox select-person" onchange="javascript:insertPerson()" ' );
					unset ( $personlist );
				}
				break;
			
			case '2' :
				{ // Select Club
					$this->assignRef ( 'clubs', $model->getClubListSelect () );
					$clublist = array ();
					$clublist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_SELECT_CLUB' ) );
					$clublist = array_merge ( $clublist, $this->clubs );
					$lists ['clubs'] = JHtml::_ ( 'select.genericlist', $clublist, 'clubID', 'class="inputbox select-club" onchange="javascript:insertClub()" ' );
					unset ( $clublist );
				}
				break;
			
			case '1' :
			default :
				{ // Select Team
					$this->assignRef ( 'teams', $model->getTeamListSelect () );
					$this->assignRef ( 'clubs', $model->getClubListSelect () );
					$teamlist = array ();
					$teamlist [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SISIMPORT_SELECT_TEAM' ) );
					$teamlist = array_merge ( $teamlist, $this->teams );
					$lists ['teams'] = JHtml::_ ( 'select.genericlist', $teamlist, 'teamID', 'class="inputbox select-team" onchange="javascript:insertTeam()" ', 'value', 'text', 0 );
					unset ( $teamlist );
				}
				break;
		}
		
		$this->assignRef ( 'lists', $lists );
		// Set page title
		$pageTitle = JText::_ ( 'COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_ASSIGN_TITLE' );
		$document->setTitle ( $pageTitle );
		
		parent::display ( $tpl );
	}
    */
    
	protected function addToolbar() {
		//JLToolBarHelper::save ( 'jlextdfbnetplayerimport.save' );
		//JToolBarHelper::custom('jlextdfbnetplayerimport.save','upload','upload',JText::_('COM_JOOMLEAGUE_ADMIN_SIS_IMPORT_UPLOAD_BUTTON'),false);
        
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_SIS_IMPORT' ),'dfbnet' );
        

	}
}

?>