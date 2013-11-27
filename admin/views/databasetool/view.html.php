<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );


class sportsmanagementViewDatabaseTool extends JView
{
	function display( $tpl = null )
	{
		$db		= JFactory::getDBO();
		$uri	= JFactory::getURI();
        $model	= $this->getModel();
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();
        
        $this->task = JRequest::getCmd('task');
        $this->step = JRequest::getvar('step',0);
        
        if ( !$this->step )
        {
            $this->step = 0;
        }

		$this->assign('request_url',$uri->toString() );
        
        switch ($this->task)
        {
            case 'truncate':
            case 'optimize':
            case 'repair':
            $this->assign('sm_tables',$model->getSportsManagementTables() );
            $this->assign('totals',count($this->sm_tables) );
            if ( $this->step < count($this->sm_tables) )
            {
            $successTable = $model->setSportsManagementTableQuery($this->sm_tables[$this->step], $this->task);    
            }
            $javascript = "\n";
            
            $javascript .= 'var joomlaupdate_progress_bar = null;' . "\n";
            $javascript .= 'var joomlaupdate_stat_percent = 0;' . "\n";
            $javascript .= 'var joomlaupdate_stat_files = 0;' . "\n";
            
            $javascript .= '// Display data' . "\n";
            $javascript .= 'joomlaupdate_progress_bar = new Fx.ProgressBar(document.id(\'progress\'));' . "\n";
            $javascript .= 'joomlaupdate_stat_percent = ('.$this->step.' * 100) / '.$this->totals.';' . "\n";
            $javascript .= 'joomlaupdate_progress_bar.set(joomlaupdate_stat_percent);' . "\n"; 
            $javascript .= 'joomlaupdate_stat_files = '.$this->totals.';'. "\n";
			$javascript .= 'document.getElementById(\'extpercent\').innerHTML = new Number(joomlaupdate_stat_percent).formatPercentage(1);' . "\n"; 
			$javascript .= 'document.getElementById(\'extfiles\').innerHTML = new Number(joomlaupdate_stat_files).format();' . "\n"; 
            
            
            $javascript .= 'jQuery(".extfiles").val('.$this->totals.');' . "\n"; 
            
            $javascript .= "\n";
            $document->addScriptDeclaration( $javascript );
            $this->step++;
            break;
        }
        
        // Load mooTools
		//JHtml::_('behavior.framework', true);
        
        // Load our Javascript
		$document = JFactory::getDocument();
		$document->addScript('../media/com_joomlaupdate/json2.js');
		$document->addScript('../media/com_joomlaupdate/encryption.js');
		$document->addScript('../media/com_joomlaupdate/update.js');
		JHtml::_('script', 'system/progressbar.js', true, true);
        JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);

		//$this->addToolbar();		
		parent::display( $tpl );
	}
    
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE' ), 'config.png' );
		JToolBarHelper::back();
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
	}	
	
}
?>