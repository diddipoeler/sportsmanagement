<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage github
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewgithub
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewgithub extends sportsmanagementView
{

	/**
	 * sportsmanagementViewgithub::init()
	 *
	 * @return void
	 */
	function init()
	{
		$this->gh_token = '';
		$this->api_username = '';
		$this->api_password = '';
		$this->issuetitle = '';
		$this->message = '';
		$this->milestone = 0;

		if ($this->app->isClient('administrator'))
		{
			$this->issuetitle = 'Backend-View: ' . $this->jinput->getCmd('issueview') . ' Layout: ' . $this->jinput->getCmd('issuelayout');
			$this->milestone = 1;
		}

		else
		{
			   $this->milestone = 2;
		}

		switch ($this->getLayout())
		{
			case 'addissue':
			case 'addissue_3':
			case 'addissue_4':
					$this->_displayAddIssue();

			return;
				   break;
			case 'github_result':
			case 'github_result_3':
			case 'github_result_4':
					$this->_displayGithubResult();

			return;
				   break;
		}

			$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/octicons.css');
			$this->commitlist = $this->model->getGithubList();

	}



	/**
	 * sportsmanagementViewgithub::_displayGithubResult()
	 *
	 * @return void
	 */
	function _displayGithubResult()
	{

			  $this->setLayout('github_result');
	}

	/**
	 * sportsmanagementViewgithub::_displayAddIssue()
	 *
	 * @return void
	 */
	function _displayAddIssue()
	{
		// Build the html select
		$myoptions = array();
		$myoptions[] = HTMLHelper::_('select.option', 'bug', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_BUG'));
		$myoptions[] = HTMLHelper::_('select.option', 'duplicate', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_DUPLICATE'));
		$myoptions[] = HTMLHelper::_('select.option', 'enhancement', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_ENHANCEMENT'));
		$myoptions[] = HTMLHelper::_('select.option', 'invalid', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_INVALID'));
		$myoptions[] = HTMLHelper::_('select.option', 'question', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_QUESTION'));
		$myoptions[] = HTMLHelper::_('select.option', 'wontfix', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_WONTFIX'));
		$lists['labels'] = HTMLHelper::_('select.genericlist', $myoptions, 'labels', 'class="form-control form-control-inline" size="6"', 'value', 'text', 'bug');

			  $myoptions = array();
		$myoptions[] = HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_FRONTEND'));
		$myoptions[] = HTMLHelper::_('select.option', '3', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_MODULES'));
		$myoptions[] = HTMLHelper::_('select.option', '4', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_EXTENSIONS'));
		$myoptions[] = HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_BACKEND'));
		$lists['milestones'] = HTMLHelper::_('select.genericlist', $myoptions, 'milestones', 'class="form-control form-control-inline" size="4"', 'value', 'text', $this->milestone);

			  $this->lists = $lists;

			  $params = ComponentHelper::getParams($this->option);

		if ($params->get('gh_token', ''))
		{
			$this->gh_token = $params->get('gh_token', '');
		}
			// Set the username and password if set in the params
		elseif ($params->get('gh_user', '') && $params->get('gh_password'))
		{
			  $this->api_username = $params->get('gh_user', '');
			  $this->api_password = $params->get('gh_password', '');
		}

			  $this->setLayout('add_issue');

	}


		 /**
		  * Add the page title and toolbar.
		  *
		  * @since 1.7
		  */
	protected function addToolbar()
	{
		  // Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_TITLE');
		  sportsmanagementHelper::ToolbarButton('addissue', 'new', Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'), 'github');
		  ToolbarHelper::back();
	}

}
