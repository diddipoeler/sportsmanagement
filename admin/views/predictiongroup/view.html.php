<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiongroup
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewpredictiongroup
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewpredictiongroup extends sportsmanagementView
{


	/**
	 * sportsmanagementViewpredictiongroup::init()
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

		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_PREDICTION_GROUP_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTION_GROUP_NEW');
		$this->icon = 'pgame';

		parent::addToolbar();
	}


}
