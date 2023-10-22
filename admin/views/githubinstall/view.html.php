<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage githubinstall
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewgithubinstall
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewgithubinstall extends sportsmanagementView
{

	/**
	 * sportsmanagementViewgithubinstall::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$github_link         = ComponentHelper::getParams($this->option)->get('cfg_update_server_file', '');
		$this->github_link   = $github_link;
		$this->_success_text = $this->model->CopyGithubLink($github_link);
		foreach ($this->_success_text as $key => $value)
		{
		$this->notes[] = $value;		
		}
		//$this->setDocument();
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	 /**
	public function setDocument($document)
	{
		$this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL'));
		$this->document->addScript(Uri::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		Text::script('COM_SPORTSMANAGEMENT_GITHUB_UPDATE');
	}
	*/
}
