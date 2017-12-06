<?php


// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
//jimport('joomla.filesystem.file');


/**
 * sportsmanagementViewTreeto
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreeto extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewTreeto::init()
     * 
     * @return
     */
    public function init ()
    //function display( $tpl = null )
	{
		//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' getLayout <br><pre>'.print_r($this->getLayout(),true).'</pre>'),'');
		if ( $this->getLayout() == 'edit' || $this->getLayout() == 'edit_3' )
		{
			$this->_displayForm(  );
			return;
		}
		elseif ($this->getLayout() == 'gennode' || $this->getLayout() == 'gennode_3' )
		{
			$this->_displayGennode();
			return;
		}
	//	parent::display( $tpl );
	}

	function _displayForm()
	{
		//$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication();
//		$db = JFactory::getDbo();
//		$uri = JFactory::getURI();
//		$user = JFactory::getUser();
//		$model = $this->getModel();
//		$lists = array();

		//$treeto = $this->get('data');
		//$script = $this->get('Script');
//		$this->script = $script;
		//if there is no image selected, use default picture
		//		$default = JoomleagueHelper::getDefaultPlaceholder("team");
		//		if (empty($treeto->trophypic)){$treeto->trophypic=$default;}

		// fail if checked out not by 'me'
		//if ($model->isCheckedOut($user->get('id')))
//		{
//			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('The treeto'),$treeto->id);
//			$app->redirect('index.php?option='.$option,$msg);
//		}

		//$this->assignRef('form' 	,$this->get('form'));
//		$this->assignRef('treeto',$treeto);

		//$this->addToolBar();
		//parent::display($tpl);
		$this->setDocument();
        //$this->setLayout('edit');  
	}

	function _displayGennode()
	{
		//$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication();
//		$db = JFactory::getDbo();
//		$uri = JFactory::getURI();
//		$user = JFactory::getUser();
//		$model = $this->getModel();
		
        $this->form = $this->get('Form');
        
        $lists = array();

		$this->treeto = $this->get('Item');
		$projectws = $this->get('Data','project');
		//$this->assignRef('form' 	,$this->get('form'));
        $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $this->projectws = $mdlProject->getProject($this->project_id);
		//$this->assignRef('projectws',$projectws);
		$this->assignRef('lists',$lists);
		//$this->assignRef('treeto',$treeto);

		$this->addToolBar_Gennode();
		//parent::display($tpl);
        $this->setLayout('gennode');  
	}

	protected function addToolBar_Gennode()
	{
		JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE_GENERATE'));
		JToolbarHelper::back('Back','index.php?option=com_sportsmanagement&view=treetos&task=treeto.display');
		//JToolbarHelper::help('screen.joomleague', true);
	}

	protected function addToolBar()
	{
		JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE'));
		JToolbarHelper::save('treeto.save');
		JToolbarHelper::apply('treeto.apply');
		JToolbarHelper::back('Back','index.php?option=com_sportsmanagement&view=treetos&task=treeto.display');
		//JToolbarHelper::help('screen.joomleague', true);
	}

	protected function setDocument()
	{
		//$document = JFactory::getDocument();
//		$version = urlencode(JoomleagueHelper::getVersion());
//		$document->addScript(JUri::root() . $this->script);
	}
}
?>