<?php


// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );



class sportsmanagementViewPredictionTemplates extends JView
{
	function display( $tpl = null )
	{
		$mainframe			= JFactory::getApplication();
    // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');
    $model = $this->getModel();
    
		//$this->prediction_id	= $mainframe->getUserState( "$option.prediction_id", '0' );
        
        $this->prediction_id	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier, 'prediction_id_select', '0' );
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPredictionTemplates prediction_id<br><pre>'.print_r($this->prediction_id,true).'</pre>'),'Notice');
        
        //$prediction_id		= (int) $mainframe->getUserState( $option . 'prediction_id' );
		$lists				= array();
		
		$uri				= JFactory::getURI();
        
        
		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
		
        //$this->prediction_id	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier, 'prediction_id', '0' );
        $mdlPredictionGame = JModel::getInstance("PredictionGame", "sportsmanagementModel");
        $mdlPredictionGames = JModel::getInstance("PredictionGames", "sportsmanagementModel");
        $mdlPredictionTemplates = JModel::getInstance("PredictionTemplates", "sportsmanagementModel");
        
        
        
        if ( $this->prediction_id )
        {
        $checkTemplates  = $mdlPredictionTemplates->checklist($this->prediction_id);    
        $predictiongame		= $mdlPredictionGame->getPredictionGame( $this->prediction_id );
        }
        
//echo '<pre>' . print_r( $predictiongame, true ) . '</pre>';
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'tmpl_filter_order','filter_order','tmpl.title','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'tmpl_filter_order_Dir','filter_order_Dir','','word');
        

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		//build the html select list for prediction games
		$predictions[] = JHTML::_( 'select.option', '0', '- ' . JText::_( 'COM_JOOMLEAGUE_GLOBAL_SELECT_PRED_GAME' ) . ' -', 'value', 'text' );
		if ( $res = $mdlPredictionGames->getPredictionGames() ) 
        { 
            $predictions = array_merge( $predictions, $res ); 
            }
            //$this->prediction_id = 0;

            
		$lists['predictions'] = JHTML::_(	'select.genericlist',
											$predictions,
											'prediction_id_select',
											'class="inputbox" onChange="this.form.submit();" ',
											'value',
											'text',
											$this->prediction_id
										);

                                        
		unset( $res );
/*
		// Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_joomleague/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
    $document->addCustomTag($stylelink);
		JToolBarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_PTMPLS_TITLE' ), 'pred-cpanel' );
        
		if ( $prediction_id > 0 )
		{
			JToolBarHelper::editListX('predictiontemplate.edit');
			//JToolBarHelper::save();  // TO BE FIXED: Marked out. Better an import Button should be added here if it is not master-template
			JToolBarHelper::divider();
			if ( ( $prediction_id > 0 ) && ( $predictiongame->master_template ) )
			{
				JToolBarHelper::deleteList();
				//JToolBarHelper::deleteList( JText::_( 'Warning: all prediction-user-data and tipps of selected member will COMPLETELY be deleted!!! This is NOT reversible!!!' ) );
			}
			else
			{
				JToolBarHelper::custom( 'predictiontemplate.reset', 'restore', 'restore', JText::_( 'COM_JOOMLEAGUE_GLOBAL_RESET' ), true );
			}
			JToolBarHelper::divider();
		}
		JLToolBarHelper::onlinehelp();

		$this->assignRef( 'user',			JFactory::getUser() );
		$this->assignRef( 'pred_id',		$prediction_id );
		$this->assignRef( 'lists',			$lists );
		$this->assignRef( 'items',			$items );
		$this->assignRef( 'pagination',		$pagination );
		$this->assignRef( 'predictiongame',	$predictiongame );
		$url=$uri->toString();
		$this->assignRef('request_url',$url);
		parent::display( $tpl );
        */
        
        $this->assign( 'user',			JFactory::getUser() );
		$this->assignRef( 'pred_id',		$this->prediction_id );
		$this->assignRef( 'lists',			$lists );
		$this->assignRef( 'items',			$items );
		$this->assignRef( 'pagination',		$pagination );
		$this->assignRef( 'predictiongame',	$predictiongame );
		$this->assign('request_url',$uri->toString());
        parent::display( $tpl );
        
	}

}
?>