<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewReferee
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewReferee extends JViewLegacy
{

	/**
	 * sportsmanagementViewReferee::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $option = $jinput->getCmd('option');

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
		$person = sportsmanagementModelPerson::getPerson(0,$model::$cfg_which_database);

		$this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		$this->config = $config;
		$this->person = $person;

		$ref = sportsmanagementModelPerson::getReferee();
		if ($ref)
		{
			$titleStr = JText::sprintf('COM_SPORTSMANAGEMENT_REFEREE_ABOUT_AS_A_REFEREE',sportsmanagementHelper::formatName(null, $ref->firstname, $ref->nickname, $ref->lastname, $this->config["name_format"]));
		}
		else
		{
			$titleStr = JText::_('COM_SPORTSMANAGEMENT_REFEREE_UNKNOWN_PROJECT');
		}

		$this->referee = $ref;
		$this->history = $model->getHistory('ASC');
		$this->title = $titleStr;

		if ($config['show_gameshistory'])
		{
			$this->games = $model->getGames();
			$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database);
		}

		if ($person)
		{
			$extended = sportsmanagementHelper::getExtended($person->extended, 'referee');
			$this->extended = $extended;
		}


/**
 * 		das benötigen wir nicht, da wir bootstrap verwenden
 *         $document->setTitle($titleStr);
 *         $view = JFactory::getApplication()->input->getVar( "view") ;
 *         $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 *         $document->addCustomTag($stylelink);
 */
        
        if ( !isset($this->config['history_table_class']) )
        {
            $this->config['history_table_class'] = 'table';
        }
        if ( !isset($this->config['career_table_class']) )
        {
            $this->config['career_table_class'] = 'table';
        }
        
        $this->headertitle = $this->title;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');

		parent::display($tpl);
	}

}
?>