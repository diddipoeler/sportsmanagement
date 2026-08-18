<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       predictiongroup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelpredictiongroup
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelpredictiongroup extends JSMModelAdmin
{
	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return boolean True on success.
	 * @since 1.6
	 */
	public function save($data)
	{
		$app  = Factory::getApplication();
		$date = Factory::getDate();
		$user = $app->getIdentity();

		$data['modified']    = $date->toSql();
		$data['modified_by'] = (int) $user->id;

		if (!parent::save($data))
		{
			return false;
		}

		$id         = (int) $this->getState($this->getName() . '.id');
		$isNew      = $this->getState($this->getName() . '.new');
		$data['id'] = $id;

		if ($isNew)
		{
			$app->enqueueMessage(Text::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id), '');
		}

		return true;
	}
}
