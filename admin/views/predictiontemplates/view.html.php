<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
defined( '_JEXEC' ) or die( 'Restricted access' );





/**
 * sportsmanagementViewPredictionTemplates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionTemplates extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPredictionTemplates::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		// Reference global application object
		$app = JFactory::getApplication();
        // JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
    // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
		$model = $this->getModel();
		$starttime = microtime(); 
    
		$this->state = $this->get('State'); 
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn = $this->state->get('list.ordering');
        
		//$this->prediction_id	= $app->getUserState( "$option.prediction_id", '0' );
        
		$this->prediction_id = $this->state->get('filter.prediction_id');
		if ( isset($this->prediction_id) )
		{
		}
		else
		{
            //$this->prediction_id = $app->getUserState( "$option.predid", '0' );
			$this->prediction_id = $jinput->post->get('filter_prediction_id', 0);
		}   
         
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prediction_id -> '.$this->prediction_id.''),'Notice');
        

        
		$lists = array();
		$uri = JFactory::getURI();
        
        
		
		
        //$this->prediction_id	= $app->getUserStateFromRequest( $option .'.'.$model->_identifier, 'prediction_id', '0' );
		$mdlPredictionGame = JModelLegacy::getInstance('PredictionGame', 'sportsmanagementModel');
		$mdlPredictionGames = JModelLegacy::getInstance('PredictionGames', 'sportsmanagementModel');
        //$mdlPredictionTemplates = JModelLegacy::getInstance("PredictionTemplates", "sportsmanagementModel");
        
        
        
		if ( isset($this->prediction_id) )
		{
		$checkTemplates = $model->checklist($this->prediction_id);    
		$predictiongame	= $mdlPredictionGame->getPredictionGame( $this->prediction_id );
		}
		else
		{
            //$this->prediction_id = $app->getUserState( "$option.predid", '0' );
			$this->prediction_id = $jinput->post->get('filter_prediction_id', 0);
		}
        
		$items = $this->get('Items');
        
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $table = JTable::getInstance('predictiontemplate', 'sportsmanagementTable');
		$this->table	= $table;
        
//echo '<pre>' . print_r( $predictiongame, true ) . '</pre>';
     



		//build the html select list for prediction games
		$predictions = array();
		$predictions[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME' ) . ' -', 'value', 'text' );
		if ( $res = $mdlPredictionGames->getPredictionGames() ) 
		{ 
			$predictions = array_merge( $predictions, $res ); 
			$this->prediction_ids	= $res;
		}
            //$this->prediction_id = 0;

          
		$lists['predictions'] = JHtml::_(	'select.genericlist', 
								$predictions, 
								'filter_prediction_id', 
								'class="inputbox" onChange="this.form.submit();" ', 
								'value', 
								'text', 
								$this->state->get('filter.prediction_id')
								);


        
        $this->user	= JFactory::getUser();
		$this->pred_id	= $this->prediction_id;
		$this->lists	= $lists;
		$this->items	= $items;
		$this->pagination	= $pagination;
		$this->predictiongame	= $predictiongame;
		$this->request_url	= $uri->toString();
       
		unset( $res );
		unset( $predictions );
		unset( $lists );
        
	}
    
    /**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
	   
		$this->title = JText::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATES');
		$this->icon = 'templates';

		JToolBarHelper::deleteList('', 'predictiontemplates.delete');
		JToolbarHelper::checkin('predictiontemplates.checkin');
		parent::addToolbar();
       
	}   

}
?>