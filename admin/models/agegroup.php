<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       agegroup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filter\OutputFilter;

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
	 * Override parent constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see   BaseDatabaseModel
	 * @since 3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}



	/**
	 * sportsmanagementModelagegroup::importAgeGroupFile()
	 *
	 * @return void
	 */
	public function importAgeGroupFile()
	{
		$databasetool = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel");
		$cpaneltool = BaseDatabaseModel::getInstance("cpanel", "sportsmanagementModel");
		$params = ComponentHelper::getParams($this->jsmoption);
		$sporttypes = $params->get('cfg_sport_types');
		$country = $params->get('cfg_country_associations');

		foreach ($sporttypes as $key => $type)
		{
			$checksporttype = $cpaneltool->checksporttype($type);
			$insert_sport_type = $databasetool->insertSportType($type);

			foreach ($country as $keyc => $typec)
			{
				$insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);
			}
		}

	}

			  /**
			   * sportsmanagementModelagegroup::saveshort()
			   *
			   * @return
			   */
	public function saveshort()
	{
		// Reference global application object
		$app = Factory::getApplication();
		$date = Factory::getDate();
		$user = Factory::getUser();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

			// Get the input
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE_NO_SELECT');
		}

		$post = Factory::getApplication()->input->post->getArray(array());

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblRound = & $this->getTable();
			$tblRound->id = $pks[$x];

			$tblRound->name    = $post['name' . $pks[$x]];

				 $tblRound->alias = OutputFilter::stringURLSafe($post['name' . $pks[$x]]);

			// Set the values
			$tblRound->modified = $date->toSql();
			$tblRound->modified_by = $user->get('id');

			if (!$tblRound->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

				return false;
			}
		}

			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_SAVE');
	}


}
