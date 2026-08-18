<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       agegroup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelagegroup
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelagegroup extends JSMModelAdmin
{
	/**
	 * Import configured age groups.
	 *
	 * @return void
	 */
	public function importAgeGroupFile()
	{
		$databasetool = BaseDatabaseModel::getInstance('databasetool', 'sportsmanagementModel');
		$cpaneltool   = BaseDatabaseModel::getInstance('cpanel', 'sportsmanagementModel');
		$params       = ComponentHelper::getParams($this->jsmoption);
		$sporttypes   = (array) $params->get('cfg_sport_types', array());
		$countries    = (array) $params->get('cfg_country_associations', array());

		foreach ($sporttypes as $type)
		{
			$cpaneltool->checksporttype($type);
			$insertSportType = $databasetool->insertSportType($type);

			foreach ($countries as $country)
			{
				$databasetool->insertAgegroup($country, $insertSportType);
			}
		}
	}

	/**
	 * Save selected age groups from the list view.
	 *
	 * @return string|false Status text on success, false on storage failure.
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

		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE_NO_SELECT');
		}

		foreach ($pks as $pk)
		{
			$table       = $this->getTable();
			$table->id   = $pk;
			$table->name = (string) ($post['name' . $pk] ?? '');
			$table->alias = OutputFilter::stringURLSafe($table->name);
			$table->modified    = $date->toSql();
			$table->modified_by = $user->id;

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

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE');
	}
}
