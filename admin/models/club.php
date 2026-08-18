<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       club.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelclub
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelclub extends JSMModelAdmin
{
	/**
	 * Return historical club logos.
	 *
	 * @param integer $club_id   Club ID.
	 * @param integer $season_id Season ID.
	 * @param integer $team_id   Team ID.
	 * @param boolean $logoonly  Legacy compatibility flag.
	 *
	 * @return array
	 */
	public function getlogohistory($club_id = 0, $season_id = 0, $team_id = 0, $logoonly = false)
	{
		$app      = Factory::getApplication();
		$db       = $this->getDatabase();
		$clubId   = (int) $club_id;
		$seasonId = (int) $season_id;
		$teamId   = (int) $team_id;
		$query    = $db->getQuery(true)
			->select('cl.*, se.name AS seasonname')
			->from($db->quoteName('#__sportsmanagement_club_logos', 'cl'))
			->join('INNER', $db->quoteName('#__sportsmanagement_season', 'se') . ' ON se.id = cl.season_id');

		if ($teamId)
		{
			$query->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON c.id = cl.club_id');
			$query->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id');
			$query->where('t.id = ' . $teamId);
		}

		if ($clubId)
		{
			$query->where('cl.club_id = ' . $clubId);
		}

		if ($seasonId)
		{
			$query->where('se.id = ' . $seasonId);
		}

		$query->order('seasonname DESC');

		try
		{
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error');
			return array();
		}
	}

	/**
	 * Return a custom extra-field value for a club.
	 *
	 * @param integer $club_id   Club ID.
	 * @param string  $fieldtext Field name fragment.
	 *
	 * @return mixed
	 */
	public function getuserextrafieldvalue($club_id = 0, $fieldtext = '')
	{
		$clubId = (int) $club_id;

		if (!$clubId || $fieldtext === '')
		{
			return null;
		}

		$this->jsmquery->clear();
		$this->jsmquery->select('uefv.fieldvalue');
		$this->jsmquery->from('#__sportsmanagement_user_extra_fields_values AS uefv');
		$this->jsmquery->join('INNER', '#__sportsmanagement_user_extra_fields AS uef ON uef.id = uefv.field_id');
		$this->jsmquery->where('uefv.jl_id = ' . $clubId);
		$this->jsmquery->where('uef.name LIKE ' . $this->jsmdb->quote('%' . $fieldtext . '%'));
		$this->jsmquery->where('uef.template_backend = ' . $this->jsmdb->quote('club'));

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			return $this->jsmdb->loadResult();
		}
		catch (RuntimeException $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			return false;
		}
	}

	/**
	 * Update selected clubs from the list view.
	 *
	 * @return boolean True on success.
	 */
	public function saveshort()
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();
		$date  = Factory::getDate();
		$user  = $app->getIdentity();
		$pks   = $input->post->get('cid', array(), 'array');
		$post  = $input->post->getArray(array());
		$pks   = array_values(array_filter(array_map('intval', (array) $pks), static fn($id) => $id > 0));

		foreach ($pks as $pk)
		{
			$addressParts = array();
			$table        = $this->getTable();

			$table->id          = $pk;
			$table->zipcode     = (string) ($post['zipcode' . $pk] ?? '');
			$table->location    = (string) ($post['location' . $pk] ?? '');
			$table->address     = (string) ($post['address' . $pk] ?? '');
			$table->country     = (string) ($post['country' . $pk] ?? '');
			$table->founded_year = (string) ($post['founded_year' . $pk] ?? '');
			$table->unique_id   = (string) ($post['unique_id' . $pk] ?? '');
			$table->new_club_id = (int) ($post['new_club_id' . $pk] ?? 0);
			$table->name        = trim((string) ($post['club_name' . $pk] ?? ''));
			$table->alias       = OutputFilter::stringURLSafe($table->name);
			$table->modified    = $date->toSql();
			$table->modified_by = $user->id;

			if ($table->address !== '')
			{
				$addressParts[] = $table->address;
			}

			if ($table->location !== '')
			{
				$addressParts[] = $table->zipcode !== ''
					? $table->zipcode . ' ' . $table->location
					: $table->location;
			}

			if ($table->country !== '')
			{
				$addressParts[] = JSMCountries::getShortCountryName($table->country);
			}

			$address = implode(', ', $addressParts);
			$coords  = $address !== '' ? sportsmanagementHelper::resolveLocation($address) : false;

			if ($coords)
			{
				$table->latitude  = $coords['latitude'];
				$table->longitude = $coords['longitude'];
			}

			if (!$table->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(
					get_class($this),
					__FUNCTION__,
					__FILE__,
					$table->getError(),
					__LINE__
				);
				return false;
			}
		}

		return true;
	}

	/**
	 * Return teams belonging to a club.
	 *
	 * @param integer $club_id Club ID.
	 *
	 * @return array|false
	 */
	public function teamsofclub($club_id)
	{
		$clubId = (int) $club_id;
		$this->jsmquery->clear();
		$this->jsmquery->select('t.id, t.name, t.club_id, t.short_name');
		$this->jsmquery->from('#__sportsmanagement_team AS t');
		$this->jsmquery->where('t.club_id = ' . $clubId);

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			return $this->jsmdb->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			return false;
		}
	}
}
