<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       smquote.php
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
 * sportsmanagementModelsmquote
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelsmquote extends JSMModelAdmin
{
	static $db_num_rows  = 0;

	/**
	 * Method to save the form data.
	 *
	 * @param  array    The form data.
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		  $app = Factory::getApplication();
		  $date = Factory::getDate();
		  $user = Factory::getUser();
		  $db = Factory::getDbo();
		$query = $db->getQuery(true);

		  $post = Factory::getApplication()->input->post->getArray(array());

		  // Set the values
		  $data['modified'] = $date->toSql();
		  $data['modified_by'] = $user->get('id');

		if (isset($post['extended']) && is_array($post['extended']))
		{
			// Convert the extended field to a string.
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		// Alter the title for Save as Copy
		if ($this->jsmjinput->get('task') == 'save2copy')
		{
			$orig_table = $this->getTable();
			$orig_table->load((int) $this->jsmjinput->getInt('id'));
			$data['id'] = 0;

			if ($data['name'] == $orig_table->name)
			{
				$data['name'] .= ' ' . Text::_('JGLOBAL_COPY');
				$data['alias'] = OutputFilter::stringURLSafe($data['name']);
			}
		}

			// Zuerst sichern, damit wir bei einer neuanlage die id haben
		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');
			$isNew = $this->getState($this->getName() . '.new');
			$data['id'] = $id;

			if ($isNew)
			{
				// Here you can do other tasks with your newly saved record...
				$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
			}

					 // Fields to update.
					$fields = array(
					$db->quoteName('picture') . ' = ' . $db->quote($data['picture'])
			);

			// Conditions for which records should be updated.
			$conditions = array(
			$db->quoteName('author') . ' LIKE ' . $db->quote($data['author'])
			);

			$query->update($db->quoteName('#__sportsmanagement_rquote'))->set($fields)->where($conditions);

			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
		}

			  return true;
	}

}
