<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       rosterposition.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Filter\OutputFilter;

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
	 * @param   array    The form data.
	 *
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();

		// Get a db connection.
		$db   = Factory::getDbo();
		$post = Factory::getApplication()->input->post->getArray(array());

		if (isset($post['extended']) && is_array($post['extended']))
		{
			// Convert the extended field to a string.
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		// Set the values
		$data['modified']    = $date->toSql();
		$data['modified_by'] = $user->get('id');
		$data['alias']       = $data['short_name'];
		//$data['alias']       = OutputFilter::stringURLSafe($data['name']);

		// Zuerst sichern, damit wir bei einer neuanlage die id haben
		if (parent::save($data))
		{
			$id         = (int) $this->getState($this->getName() . '.id');
			$isNew      = $this->getState($this->getName() . '.new');
			$data['id'] = $id;

			if ($isNew)
			{
				// Here you can do other tasks with your newly saved record...
				$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
			}
		}

		return true;
	}


}
