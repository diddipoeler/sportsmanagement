<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionmembers
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewPredictionMembers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionMembers extends sportsmanagementView
{
  
  /**
   * sportsmanagementViewPredictionMembers::init()
   * 
   * @return
   */
  public function init ()
	{
	   
//       // Reference global application object
//		$app = JFactory::getApplication();
//        // JInput object
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$this->state = $this->get('State');
		$tpl = '';
       
		$this->prediction_id = $this->state->get('filter.prediction_id');
       
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout<br><pre>'.print_r($this->getLayout(),true).'</pre>'),'');

	if ( $this->getLayout() == 'default' || $this->getLayout() == 'default_3' )
		{
			//$app->setUserState( "$option.prediction_id", $this->state->get('filter.prediction_id') );
			$this->_display( $tpl );
			return;
		}
		
		if ( $this->getLayout() == 'editlist' || $this->getLayout() == 'editlist_3' )
		{
			$this->_editlist( $tpl );
			return;
		}
    
    
		
	}

  /**
   * sportsmanagementViewPredictionMembers::_editlist()
   * 
   * @param mixed $tpl
   * @return void
   */
  function _editlist( $tpl = null )
	{
		// Reference global application object
		$app = JFactory::getApplication();
		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
    //$db					= sportsmanagementHelper::getDBConnection();
		$uri = JFactory::getURI();
		
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
    
		$this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        		
		//$prediction_id		= (int) $app->getUserState( $option . '.prediction_id' );
		$prediction_name	= $this->getModel()->getPredictionProjectName($this->prediction_id);
		$this->prediction_name	= $prediction_name;
		
	$res_prediction_members = $this->getModel()->getPredictionMembers($this->prediction_id);
    
	if ( $res_prediction_members )
		{
			$lists['prediction_members'] = JHtml::_(	'select.genericlist', 
										$res_prediction_members, 
										'prediction_members[]', 
										'class="inputbox" multiple="true" onchange="" size="15"', 
										'value', 
										'text');
	}
	else
		{
			$lists['prediction_members'] = '<select name="prediction_members[]" id="prediction_members" style="" class="inputbox" multiple="true" size="15"></select>';
		}
    
    $res_joomla_members = $this->getModel()->getJLUsers($this->prediction_id);
    if ( $res_joomla_members )
    {
		$lists['members'] = JHtml::_(	'select.genericlist', 
										$res_joomla_members, 
										'members[]', 
										'class="inputbox" multiple="true" onchange="" size="15"', 
										'value', 
										'text');
    }
                    																
	//$this->prediction_id	= $prediction_id ;
	$this->lists	= $lists;
	$this->request_url	= $uri->toString();
    $this->user	= JFactory::getUser();
		$this->setlayout('editlist');
        
	}	

	
    /**
     * sportsmanagementViewPredictionMembers::_display()
     * 
     * @param mixed $tpl
     * @return void
     */
	function _display( $tpl = null )
	{
//// Reference global application object
//		$app = JFactory::getApplication();
//        // JInput object
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');
//		$document = JFactory::getDocument();
//		$model	= $this->getModel();
//    	$uri = JFactory::getURI();
        
//		$this->state = $this->get('State'); 
//        $this->sortDirection = $this->state->get('list.direction');
//        $this->sortColumn = $this->state->get('list.ordering');

//		$items = $this->get('Items');
//		$total = $this->get('Total');
//		$pagination = $this->get('Pagination');
        
		$table = JTable::getInstance('predictionmember', 'sportsmanagementTable');
		$this->table = $table;

        //build the html select list for prediction games
        $mdlPredGames = JModelLegacy::getInstance('PredictionGames', 'sportsmanagementModel');
		$predictions[] = JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME' ), 'value', 'text' );
		if ( $res = $mdlPredGames->getPredictionGames() ) 
			{ 
				$predictions = array_merge( $predictions, $res ); 
				$this->prediction_ids = $res;
			}
			
		$lists['predictions'] = JHtml::_('select.genericlist', 
								$predictions, 
								'filter_prediction_id', 
								'class="inputbox" onChange="this.form.submit();" ', 
								'value', 
								'text', 
								$this->state->get('filter.prediction_id')
								);
		unset( $res );
      
		//$this->user	= JFactory::getUser();
		$this->lists	= $lists;
//		$this->pagination	= $pagination;
//		$this->items	= $items;
//		$this->request_url	= $uri->toString();
        
        
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{

		$this->title = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_TITLE' );
        
		JToolbarHelper::custom('predictionmembers.reminder', 'send.png', 'send_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER' ), true );
		JToolbarHelper::divider();
        
		if ( $this->prediction_id )
			{
				sportsmanagementHelper::ToolbarButton('editlist', 'new', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_BUTTON_ASSIGN') );
				JToolbarHelper::publishList('predictionmembers.publish', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVE' ) );
				JToolbarHelper::unpublishList('predictionmembers.unpublish', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REJECT' ) );
				JToolbarHelper::deleteList( '','predictionmembers.remove' );  
			}
				JToolbarHelper::checkin('predictionmembers.checkin');

        
		parent::addToolbar();
        
		
	}
    
    
    

}
?>
