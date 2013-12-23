<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 

/**
 * sportsmanagementViewcpanel
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewcpanel extends JView
{
	/**
	 *  view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model	= $this->getModel();
        
        $databasetool = JModel::getInstance("databasetool", "sportsmanagementModel");
        DEFINE( 'COM_SPORTSMANAGEMENT_MODEL_ERRORLOG',$databasetool );
        
        $params = JComponentHelper::getParams( $option );
        $sporttypes = $params->get( 'cfg_sport_types' );
        $sm_quotes = $params->get( 'cfg_quotes' );
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($sm_quotes,true).'</pre>'),'Notice');
        
        // zitate
        $databasetool->checkQuotes($sm_quotes);
        
        foreach ( $sporttypes as $key => $type )
        {
        $checksporttype = $model->checksporttype($type);    
        
        $checksporttype_strukt = $databasetool->checkSportTypeStructur($type);
        
        if ( $checksporttype )
        {
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_SUCCESS',strtoupper($type)),'');    
        
        // es können aber auch neue positionen oder ereignisse dazu kommen
        $insert_sport_type = $databasetool->insertSportType($type); 
        
        }
        else
        {
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_ERROR',strtoupper($type)),'Error');
        
        $insert_sport_type = $databasetool->insertSportType($type);    
        //$checksporttype_strukt = $databasetool->checkSportTypeStructur($type);
        }
        
        }
        
        // Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
        
        $checkassociations = $databasetool->checkAssociations();
        
        $checkcountry = $model->checkcountry();
        if ( $checkcountry )
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS'),'');    
        }
        else
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR'),'Error'); 
        $insert_countries = $databasetool->insertCountries();    
        }
        
		jimport('joomla.html.pane');
		$pane	= JPane::getInstance('sliders');
		$this->assignRef( 'pane'		, $pane );
        $this->assignRef( 'sporttypes'		, $sporttypes );
        $this->assign( 'version', $model->getVersion() );
        $this->assign( 'githubrequest', $model->getGithubRequests() );
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$canDo = sportsmanagementHelper::getActions();
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_MANAGER'), 'helloworld');
		
		if ($canDo->get('core.admin')) 
		{
			//JToolBarHelper::custom('cpanel.import','upload','upload',JText::_('JTOOLBAR_INSTALL'),false);
            sportsmanagementHelper::ToolbarButton('default','upload',JText::_('JTOOLBAR_INSTALL'),'githubinstall',1);
            JToolBarHelper::divider();
            sportsmanagementHelper::ToolbarButtonOnlineHelp();
			JToolBarHelper::preferences('com_sportsmanagement');
		}
	}
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMINISTRATION'));
	}
	
	public function addIcon( $image , $url , $text , $newWindow = false , $width = 0, $height = 0)
	{
		$lang		= JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
        $attribs = array();
        if ( $width )
        {
        $attribs['width'] = $width;
        $attribs['height'] = $height;
        }
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image , null, $attribs ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
	
}
