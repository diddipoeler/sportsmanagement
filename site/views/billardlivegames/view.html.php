<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage billardlivegames
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewAbout
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewbillardlivegames extends sportsmanagementView
{

	/**
	 * sportsmanagementViewAbout::init()
	 *
	 * @return void
	 */
	function init()
	{

		//$about       = $this->model->getAbout();
		//$this->about = $about;

		// Set page title
		$this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_BILLARDLIVEGAMES_PAGE_TITLE'));
	}

}

