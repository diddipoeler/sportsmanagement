<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage predictionround
 * @file       view.html.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPredictionRound
 *
 * @package
 * @author    jst
 * @copyright 2020
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewPredictionRound extends sportsmanagementView
{


	/**
	 * sportsmanagementViewPredictionRound::init()
	 *
	 * @return
	 */
	public function init()
	{

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors));

			return false;
		}

	}


	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{

		$jinput = Factory::getApplication()->input;
		$jinput->set('hidemainmenu', true);

		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_PREDICTION_ROUND_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUND_NEW');
		$this->icon = 'pgame';

		parent::addToolbar();
	}


}
