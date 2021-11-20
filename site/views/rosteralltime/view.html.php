<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rosteralltime
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/** Prüft vor Benutzung ob die gewünschte Klasse definiert ist */
if (!class_exists('sportsmanagementHelperHtml'))
{
	/** Add the classes for handling */
	$classpath = JPATH_SITE . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html.php';
	JLoader::register('sportsmanagementHelperHtml', $classpath);
}

/**
 * sportsmanagementViewRosteralltime
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewRosteralltime extends sportsmanagementView
{

	/**
	 * sportsmanagementViewRosteralltime::init()
	 * 
	 * @return void
	 */
	function init()
	{
		$this->jsmstartzeit = $this->getStartzeit();
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->playerposition     = $this->model->getPlayerPosition();
		$this->positioneventtypes = $this->model->getPositionEventTypes();
		$this->rows = $this->model->getTeamPlayers(1, $this->positioneventtypes, $this->items);
        
        $form             = new stdClass;
		$form->limitField = $this->pagination->getLimitBox();
		$this->filter     = $this->state->get('filter.search');
		$this->form       = $form;
		$this->sortDirection = $this->state->get('filter_order_Dir');
		$this->sortColumn    = $this->state->get('filter_order');
        
        $this->tips = sportsmanagementModelRosteralltime::$_tips;
        $this->warnings = sportsmanagementModelRosteralltime::$_warnings;
        $this->notes = sportsmanagementModelRosteralltime::$_notes;

	}

}
