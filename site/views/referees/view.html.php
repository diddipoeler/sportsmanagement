<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

class sportsmanagementViewReferees extends JViewLegacy
{

	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();

		$model	= $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		
		if ( !$config )
		{
			$config	= sportsmanagementModelProject::getTemplateConfig( 'players' );
		}

		$this->assignRef( 'project', sportsmanagementModelProject::getProject() );
		$this->assignRef( 'overallconfig', sportsmanagementModelProject::getOverallConfig() );
		$this->assignRef( 'config', $config );

		$this->assignRef( 'rows', $model->getReferees() );
//		$this->assignRef( 'positioneventtypes', $model->getPositionEventTypes( ) );

		// Set page title
		$pagetitle=JText::_( 'COM_SPORTSMANAGEMENT_REFEREES_PAGE_TITLE' );
		$document->setTitle( JText::sprintf( $pagetitle, $this->project->name ) );

		parent::display( $tpl );
	}

}
?>