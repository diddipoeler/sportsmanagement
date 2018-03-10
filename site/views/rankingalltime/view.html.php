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
 * OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License f�r weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */
defined('_JEXEC') or die('Restricted access');

//require_once (JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

jimport('joomla.application.component.view');

//require_once (JLG_PATH_ADMIN . DS . 'models' . DS . 'divisions.php');

/**
 * sportsmanagementViewRankingAllTime
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewRankingAllTime extends JViewLegacy {

    /**
     * sportsmanagementViewRankingAllTime::display()
     * 
     * @param mixed $tpl
     * @return void
     */
    function display($tpl = null) {
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();

        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }

        $document->addScript(JUri::root(true) . '/components/' . $option . '/assets/js/smsportsmanagement.js');

        $model = $this->getModel();

        $this->projectids = $model->getAllProject();
        $project_ids = implode(",", $this->projectids);

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_ids<br><pre>'.print_r($project_ids,true).'</pre>'),'');

        $this->project_ids = $project_ids;
        $this->teams = $model->getAllTeamsIndexedByPtid($project_ids);

        $this->matches = $model->getAllMatches($project_ids);
        $this->ranking = $model->getAllTimeRanking();
        $this->tableconfig = $model->getAllTimeParams();
        $this->config = $model->getAllTimeParams();

        $this->currentRanking = $model->getCurrentRanking();

        $this->action = $uri->toString();
        $this->colors = $model->getColors($this->config['colors']);



        // Set page title
        $pageTitle = JText::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');

        $document->setTitle($pageTitle);

        parent::display($tpl);
    }

}

?>