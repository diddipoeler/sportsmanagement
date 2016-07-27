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
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewPredictionGames
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionGames extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPredictionGames::init()
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
		$model = $this->getModel();
		$starttime = microtime();
		$document = JFactory::getDocument();
    
	$uri = JFactory::getURI();
    
	$this->state = $this->get('State');
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn = $this->state->get('list.ordering');
    
		//$prediction_id		= (int) $app->getUserState( $option . 'prediction_id' );
        //$this->prediction_id	= $app->getUserState( "$option.prediction_id", '0' );
		$modalheight = JComponentHelper::getParams($option)->get('modal_popup_height', 600);
		$modalwidth = JComponentHelper::getParams($option)->get('modal_popup_width', 900);
		$this->modalheight	= $modalheight;
		$this->modalwidth	= $modalwidth;
        
        //echo '#' . $prediction_id . '#<br />';
    
    
		$lists = array();

        //$this->prediction_id = $jinput->getVar('prediction_id', 0);
		$this->prediction_id = $this->state->get('filter.prediction_id');
		if ( $this->prediction_id != 0 )
			{
			}
		else
			{
            //$this->prediction_id = $app->getUserState( "$option.predid", '0' );
				$this->prediction_id = $jinput->request->get('prediction_id', 0);
			} 

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prediction_id<br><pre>'.print_r($this->prediction_id,true).'</pre>'),'');        
        
        //$this->prediction_id	= $app->getUserStateFromRequest( $option .'.'.$model->_identifier, 'prediction_id', '0' );
        //$app->enqueueMessage(JText::_('sportsmanagementViewPredictionGames prediction_id<br><pre>'.print_r($this->prediction_id,true).'</pre>'),'Notice');

$items = $this->get('Items');

if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $table = JTable::getInstance('predictiongame', 'sportsmanagementTable');
		$this->table	= $table;
        
		if ( !$items )
			{
				$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'),'Error');
			}
        


		//build the html select list for prediction games
		$predictions[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME' ), 'value', 'text' );
		if ( $res = $model->getPredictionGames() )
        { 
			$predictions = array_merge( $predictions, $res );
			$this->prediction_ids	= $res;
		}
		
		$lists['predictions'] = JHtml::_(	'select.genericlist', 
											$predictions, 
											'filter_prediction_id', 
											'class="inputbox" onChange="this.form.submit();" ', 
											'value', 
											'text', 
											$this->state->get('filter.prediction_id')
										);
		unset( $res );


		$this->user	= JFactory::getUser();
		$this->lists	= $lists;
        $this->option	= $option;
		$this->items	= $items ;
		$this->dPredictionID	= $this->prediction_id;
		$this->pagination	= $pagination;
		
		if ( $this->prediction_id > 0 )
		{
			$this->predictionProjects	= $this->getModel()->getChilds( $this->prediction_id );
		}

    
		$this->request_url	= $uri->toString();
        
       
    
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{ 
		
   
        // Set toolbar items for the page
		$this->title = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE' );
		$this->icon = 'pred-cpanel';
		JToolBarHelper::publish('predictiongames.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('predictiongames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
      
		JToolBarHelper::editList('predictiongame.edit');
		JToolBarHelper::addNew('predictiongame.add');
		JToolBarHelper::custom('predictiongame.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('predictiongame.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('','predictiongames.delete', 'JTOOLBAR_DELETE');
		JToolbarHelper::checkin('predictiongroups.checkin');
		parent::addToolbar();  
        
	}
    
    

}
?>