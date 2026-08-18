<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       jsmgcalendar.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * sportsmanagementModeljsmGCalendar
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModeljsmGCalendar extends AdminModel
{
	/**
	 * Get the calendar table.
	 *
	 * @param string $type   Table type.
	 * @param string $prefix Table class prefix.
	 * @param array  $config Table configuration.
	 *
	 * @return Table
	 */
	public function getTable($type = 'jsmGCalendar', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the edit form.
	 *
	 * @param array   $data     Form data.
	 * @param boolean $loadData Whether to load stored data.
	 *
	 * @return mixed
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_sportsmanagement.jsmGCalendar',
			'jsmGCalendar',
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param array $data The form data.
	 *
	 * @return boolean True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$config = Factory::getConfig();
		$post   = $input->post->getArray(array());

		if (isset($post['extended']) && is_array($post['extended']))
		{
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['params'] = (string) $parameter;
		}

		if (empty($data['id']))
		{
			$file   = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'createcal.xml';
			$output = "<entry xmlns='http://www.w3.org/2005/Atom'\n";
			$output .= "xmlns:gd='http://schemas.google.com/g/2005'\n";
			$output .= "xmlns:gCal='http://schemas.google.com/gCal/2005'>\n";
			$output .= "<title type='text'>[TITLE]</title>\n";
			$output .= "<summary type='text'>[SUMMARY]</summary>\n";
			$output .= "<gCal:timezone value='" . htmlspecialchars((string) $config->get('offset', 'UTC'), ENT_QUOTES, 'UTF-8') . "'></gCal:timezone>\n";
			$output .= "<gCal:hidden value='false'></gCal:hidden>\n";
			$output .= "<gCal:color value='#" . htmlspecialchars((string) ($data['color'] ?? ''), ENT_QUOTES, 'UTF-8') . "'></gCal:color>\n";
			$output .= "<gd:where rel='' label='' valueString='Oakland'></gd:where>\n";
			$output .= "</entry>\n";

			if (file_put_contents($file, $output) === false)
			{
				return false;
			}
		}

		return parent::save($data);
	}

	/**
	 * Check whether an existing calendar can be edited.
	 *
	 * @param array  $data Input data.
	 * @param string $key  Primary key field.
	 *
	 * @return boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$id = (int) ($data[$key] ?? 0);

		return Factory::getApplication()->getIdentity()->authorise(
			'core.edit',
			'com_sportsmanagement.calendar.' . $id
		) || parent::allowEdit($data, $key);
	}

	/**
	 * Get form data from the session or current record.
	 *
	 * @return mixed
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.jsmGCalendar.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
