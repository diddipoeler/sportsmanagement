<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       rosterposition.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * sportsmanagementModelrosterposition
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelrosterposition extends JSMModelAdmin
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
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$option = $input->getCmd('option', 'com_sportsmanagement');
		$date   = Factory::getDate();
		$user   = $app->getIdentity();
		$post   = $input->post->getArray();

		if (isset($post['extended']) && is_array($post['extended']))
		{
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		$data['modified']    = $date->toSql();
		$data['modified_by'] = (int) $user->id;
		$data['alias']       = $data['short_name'];

		if (!parent::save($data))
		{
			return false;
		}

		$id         = (int) $this->getState($this->getName() . '.id');
		$isNew      = $this->getState($this->getName() . '.new');
		$data['id'] = $id;

		if ($isNew)
		{
			$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
		}

		return true;
	}
}
