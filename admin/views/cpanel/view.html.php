<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 

class sportsmanagementViewcpanel extends JView
{
	/**
	 *  view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
        $model	= $this->getModel();
        
		jimport('joomla.html.pane');
		$pane	= JPane::getInstance('sliders');
		$this->assignRef( 'pane'		, $pane );
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
	
	public function addIcon( $image , $url , $text , $newWindow = false )
	{
		$lang		= JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
	
}
