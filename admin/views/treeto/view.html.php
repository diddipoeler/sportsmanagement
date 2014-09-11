<?php
/**
 * @copyright	Copyright (C) 2006-2014 joomleague.at. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
//jimport('joomla.filesystem.file');

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package		Joomleague
 * @since 0.1
*/
class JoomleagueViewTreeto extends JLGView
{
	function display( $tpl = null )
	{
		$mainframe = JFactory::getApplication();
		if ( $this->getLayout() == 'form' )
		{
			$this->_displayForm( $tpl );
			return;
		}
		elseif ($this->getLayout() == 'gennode')
		{
			$this->_displayGennode($tpl);
			return;
		}
		parent::display( $tpl );
	}

	function _displayForm($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDbo();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
		$model = $this->getModel();
		$lists=array();

		$treeto = $this->get('data');
		$script = $this->get('Script');
		$this->script = $script;
		//if there is no image selected, use default picture
		//		$default = JoomleagueHelper::getDefaultPlaceholder("team");
		//		if (empty($treeto->trophypic)){$treeto->trophypic=$default;}

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id')))
		{
			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('The treeto'),$treeto->id);
			$mainframe->redirect('index.php?option='.$option,$msg);
		}

		$this->assignRef('form' 	,$this->get('form'));
		$this->assignRef('treeto',$treeto);

		$this->addToolBar();
		parent::display($tpl);
		$this->setDocument();
	}

	function _displayGennode($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDbo();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
		$model = $this->getModel();
		$lists=array();

		$treeto = $this->get('data');
		$projectws = $this->get('Data','project');
		$this->assignRef('form' 	,$this->get('form'));
		$this->assignRef('projectws',$projectws);
		$this->assignRef('lists',$lists);
		$this->assignRef('treeto',$treeto);

		$this->addToolBar_Gennode();
		parent::display($tpl);
	}

	protected function addToolBar_Gennode()
	{
		JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_TREETO_TITLE_GENERATE'));
		JToolBarHelper::back('Back','index.php?option=com_joomleague&view=treetos&task=treeto.display');
		JToolBarHelper::help('screen.joomleague', true);
	}

	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_TREETO_TITLE'));
		JLToolBarHelper::save('treeto.save');
		JLToolBarHelper::apply('treeto.apply');
		JToolBarHelper::back('Back','index.php?option=com_joomleague&view=treetos&task=treeto.display');
		JToolBarHelper::help('screen.joomleague', true);
	}

	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$version = urlencode(JoomleagueHelper::getVersion());
		$document->addScript(JUri::root() . $this->script);
	}
}
?>