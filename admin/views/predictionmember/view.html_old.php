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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );



/**
 * sportsmanagementViewPredictionMember
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionMember extends sportsmanagementView
{
	function display( $tpl = null )
	{
		$mainframe	=& JFactory::getApplication();

		if ( $this->getLayout() == 'form' )
		{
			$this->_displayForm( $tpl );
			return;
		}

		//get the predictionuser
		$predictionuser =& $this->get( 'data' );

		parent::display( $tpl );
	}

	function _displayForm( $tpl )
	{
		$mainframe			=& JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();

		$lists = array();
		//get the member data
		$predictionuser	=& $this->get( 'data' );
		$isNew			= ( $predictionuser->id < 1 );

		

		// Edit or Create?
		if ( !$isNew )
		{
			//$model->checkout( $user->get( 'id' ) );
		}
		else
		{
			// initialise new record
			$predictionuser->order = 0;
		}

		
/*        
        //build the html select list for parent positions
		$parents[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'Prediction Group' ) . ' -' );
		if ( $res =& $model->getPredictionGroups() )
		{
			$parents = array_merge( $parents, $res );
		}
		$lists['parents'] = JHtml::_(	'select.genericlist', $parents, 'group_id', 'class="inputbox" size="1"', 'value', 'text',
										$predictionuser->group_id );
		unset( $parents );
*/
		//$this->assignRef( 'lists',			$lists );
        $this->assignRef('form'      	, $this->get('form'));
		$this->assignRef( 'predictionuser',	$predictionuser );

		parent::display( $tpl );
	}

}
?>