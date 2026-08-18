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
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

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
	static $db_num_rows = 0;

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
		$app      = Factory::getApplication();
		$input    = $app->getInput();
		$date     = Factory::getDate();
		$user     = $app->getIdentity();
		$db       = $this->getDatabase();
		$query    = $db->getQuery(true);
		$post     = $input->post->getArray();
		$option   = $input->getCmd('option', 'com_sportsmanagement');

		$data['modified']    = $date->toSql();
		$data['modified_by'] = (int) $user->id;

		if (isset($post['extended']) && is_array($post['extended']))
		{
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

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

		if (parent::save($data))
		{
			$id         = (int) $this->getState($this->getName() . '.id');
			$isNew      = $this->getState($this->getName() . '.new');
			$data['id'] = $id;

			if ($isNew)
			{
				$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
			}

			$fields = array(
				$db->quoteName('picture') . ' = ' . $db->quote($data['picture'])
			);
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
