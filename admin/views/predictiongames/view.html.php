<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );



class sportsmanagementViewPredictionGames extends JView
{
	function display( $tpl = null )
	{
		$mainframe = JFactory::getApplication();
    $model = $this->getModel();
    
		$document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');
    $uri = JFactory::getURI();
    
		//$prediction_id		= (int) $mainframe->getUserState( $option . 'prediction_id' );
        //$this->prediction_id	= $mainframe->getUserState( "$option.prediction_id", '0' );
        $modalheight = JComponentHelper::getParams($option)->get('modal_popup_height', 600);
        $modalwidth = JComponentHelper::getParams($option)->get('modal_popup_width', 900);
		$this->assignRef( 'modalheight',$modalheight );
        $this->assignRef( 'modalwidth',$modalwidth );
        
        //echo '#' . $prediction_id . '#<br />';
    
    
		$lists				= array();
		
		
        
		$filter_state		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'pre_filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'pre_filter_order','filter_order','pre.name','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'pre_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'pre_search','search','','string');
		$search				= JString::strtolower( $search );
        
        $this->prediction_id	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier, 'prediction_id', '0' );
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPredictionGames prediction_id<br><pre>'.print_r($this->prediction_id,true).'</pre>'),'Notice');

$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        if ( !$items )
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'),'Error');    
        }
        
		// state filter
		$lists['state']		= JHtml::_( 'grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search'] = $search;

		//build the html select list for prediction games
		$predictions[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'Select Prediction Game' ) . ' -', 'value', 'text' );
		if ( $res = $model->getPredictionGames() ) 
        { 
            $predictions = array_merge( $predictions, $res ); 
            }
		$lists['predictions'] = JHtml::_(	'select.genericlist',
											$predictions,
											'prediction_id',
											//'class="inputbox validate-select-required" ',
											'class="inputbox" onChange="this.form.submit();" ',
											//'class="inputbox" onChange="this.form.submit();" style="width:200px"',
											'value',
											'text',
											$this->prediction_id
										);
		unset( $res );

/*
		// Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_joomleague/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
    $document->addCustomTag($stylelink);
    */
		
        /*
        if ($prediction_id==0)
		{
			JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_PGAMES_TITLE'),'pred-cpanel');

			JToolBarHelper::publishList('predictiongame.publish');
			JToolBarHelper::unpublishList('predictiongame.unpublish');
			JToolBarHelper::divider();

			JToolBarHelper::addNew('predictiongame.add');
			JToolBarHelper::editList('predictiongame.edit');
			//JToolBarHelper::custom( 'copy', 'copy.png', 'copy_f2.png', JText::_( 'Copy'), true );
			JToolBarHelper::divider();
			//JToolBarHelper::deleteList( JText::_('JL_ADMIN_PGAMES_DELETE'));
            JToolBarHelper::deleteList( JText::_('COM_JOOMLEAGUE_ADMIN_PGAMES_DELETE'), 'predictiongame.remove');
			JToolBarHelper::divider();
			JToolBarHelper::customX('rebuild','restore.png','restore_f2.png',JText::_('COM_JOOMLEAGUE_ADMIN_PGAMES_REBUILDS'),true);
		}
		else
		{
			
            
            JToolBarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_PGAMES_PROJLIST_TITLE' ), 'pred-cpanel' );

			
		}

		JToolBarHelper::divider();
		JLToolBarHelper::onlinehelp();
*/
		$this->assign( 'user',			JFactory::getUser() );
		$this->assignRef( 'lists',			$lists );
        $this->assignRef( 'option',			$option );
		$this->assignRef( 'items',			$items );
		$this->assignRef( 'dPredictionID',	$this->prediction_id );
		$this->assignRef( 'pagination',		$pagination );
		
		if ( $this->prediction_id > 0 )
		{
			$this->assignRef( 'predictionProjects',	$this->getModel()->getChilds( $this->prediction_id ) );
			//$this->assignRef( 'predictionAdmins',	$this->getModel()->getAdmins( $prediction_id ) );
		}

    
		$this->assign('request_url',$uri->toString());
    $this->addToolbar();
		parent::display( $tpl );
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{ 
		
        // Get a refrence of the page instance in joomla
		$document	=& JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
        JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE' ), 'pred-cpanel' );
        JToolBarHelper::publish('predictiongames.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('predictiongames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
      
        JToolBarHelper::editList('predictiongame.edit');
		JToolBarHelper::addNew('predictiongame.add');
		JToolBarHelper::custom('predictiongame.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('predictiongame.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('','predictiongames.delete', 'JTOOLBAR_DELETE');
        
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
        
		
	}
    
    

}
?>