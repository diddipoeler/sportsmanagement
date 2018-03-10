<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiontemplates
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
		$starttime = microtime(); 
        
		$this->prediction_id = $this->state->get('filter.prediction_id');
		if ( isset($this->prediction_id) )
		{
		}
		else
		{
			$this->prediction_id = $this->jinput->post->get('filter_prediction_id', 0);
		}   
        
		$lists = array();

		$mdlPredictionGame = JModelLegacy::getInstance('PredictionGame', 'sportsmanagementModel');
		$mdlPredictionGames = JModelLegacy::getInstance('PredictionGames', 'sportsmanagementModel');
 
		if ( isset($this->prediction_id) )
		{
		$checkTemplates = $this->model->checklist($this->prediction_id);    
		$predictiongame	= $mdlPredictionGame->getPredictionGame( $this->prediction_id );
		}
		else
		{
			$this->prediction_id = $this->jinput->post->get('filter_prediction_id', 0);
		}
       
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
       
        $table = JTable::getInstance('predictiontemplate', 'sportsmanagementTable');
		$this->table	= $table;
        
		//build the html select list for prediction games
		$predictions = array();
		$predictions[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME' ) . ' -', 'value', 'text' );
		if ( $res = $mdlPredictionGames->getPredictionGames() ) 
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

		$this->pred_id	= $this->prediction_id;
		$this->lists	= $lists;
		$this->predictiongame	= $predictiongame;
       
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

		JToolbarHelper::deleteList('', 'predictiontemplates.delete');
		JToolbarHelper::checkin('predictiontemplates.checkin');
		parent::addToolbar();
       
	}   

}
?>
