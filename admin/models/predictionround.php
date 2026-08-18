<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage models
 * @file       predictionrounds.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelPredictionRound
 *
 * @package
 * @author    jst
 * @copyright 2020
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelPredictionRound extends JSMModelAdmin
{
	/**
	 * Method to update checked PredictionRound from admin form (POST data).
	 *
	 * @param array $pks  Items to update.
	 * @param array $post Updated form data.
	 *
	 * @return string|false Message to display or false on failure.
	 */
	public function saveshort(&$pks, &$post)
	{
		$date = Factory::getDate();
		$user = Factory::getApplication()->getIdentity();

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblRound                          = $this->getTable();
			$tblRound->id                      = $pks[$x];
			$tblRound->rien_ne_va_plus         = $post['rien_ne_va_plus' . $pks[$x]];
			$tblRound->points_tipp             = $post['points_tipp' . $pks[$x]];
			$tblRound->points_correct_result   = $post['points_correct_result' . $pks[$x]];
			$tblRound->points_correct_diff     = $post['points_correct_diff' . $pks[$x]];
			$tblRound->points_correct_draw     = $post['points_correct_draw' . $pks[$x]];
			$tblRound->points_correct_tendence = $post['points_correct_tendence' . $pks[$x]];
			$tblRound->modified                = $date->toSql();
			$tblRound->modified_by             = (int) $user->id;

			if (!$tblRound->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(
					get_class($this),
					__FUNCTION__,
					__FILE__,
					(string) $tblRound->getError(),
					__LINE__
				);
				return false;
			}
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE');
	}

	/**
	 * Method to add prediction rounds from parent JSM project.
	 * New items are added unpublished and using default parameters.
	 *
	 * @param array $projRoundsIdsToAdd Project round ids to add.
	 * @param int   $prediction_id      Parent prediction project id.
	 * @param int   $project_id         Parent SportsManagement project id.
	 *
	 * @return string|false Message to display or false on failure.
	 */
	public function addPredRoundIds($projRoundsIdsToAdd, $prediction_id, $project_id)
	{
		$date = Factory::getDate();
		$user = Factory::getApplication()->getIdentity();
		$cnt  = 0;

		foreach ($projRoundsIdsToAdd as $projRoundsIdToAdd)
		{
			$tblRound                = $this->getTable();
			$tblRound->prediction_id = $prediction_id;
			$tblRound->project_id    = $project_id;
			$tblRound->round_id      = $projRoundsIdToAdd;
			$tblRound->modified      = $date->toSql();
			$tblRound->modified_by   = (int) $user->id;
			$tblRound->published     = 0;

			if (!$tblRound->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(
					get_class($this),
					__FUNCTION__,
					__FILE__,
					(string) $tblRound->getError(),
					__LINE__
				);
				return false;
			}

			$cnt++;
		}

		return Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_ADDED', $cnt);
	}
}
