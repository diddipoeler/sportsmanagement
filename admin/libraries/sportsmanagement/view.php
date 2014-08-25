<?php

defined('_JEXEC') or die();

/**
 * sportsmanagementView
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementView extends JViewLegacy
{

	protected $icon = '';

	protected $title = '';

	/**
	 * sportsmanagementView::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function display ($tpl = null)
	{
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if (sportsmanagementHelper::isJoomlaVersion('2.5'))
		{
			// wir lassen das layout so wie es ist, dann müssen
            // nicht so viele dateien umbenannt werden
            //$this->setLayout($this->getLayout() . '_25');
            $this->setLayout($this->getLayout() );
		}
		if (sportsmanagementHelper::isJoomlaVersion('3'))
		{
			$this->setLayout($this->getLayout() . '_3');
		}

		$this->init();

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * sportsmanagementView::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar ()
	{
		$canDo = sportsmanagementHelper::getActions();

		if (empty($this->title))
		{
			$this->title = 'COM_SPORTSMANAGEMENT_MANAGER_' . strtoupper($this->getName());
		}
		if (empty($this->icon))
		{
			$this->icon = strtolower($this->getName());
		}
		JToolBarHelper::title(JText::_($this->title), $this->icon);
		$document = JFactory::getDocument();
		$document->addStyleDeclaration(
				'.icon-48-' . $this->icon . ' {background-image: url(../media/com_sportsmanagement/images/admin/48-' . $this->icon .
						 '.png);background-repeat: no-repeat;}');

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_sportsmanagement');
			JToolBarHelper::divider();
		}
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
	}

	/**
	 * sportsmanagementView::init()
	 * 
	 * @return void
	 */
	protected function init ()
	{
	}
}
