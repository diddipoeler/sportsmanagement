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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');


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
	 * Method to update checked PredictionRound from admin form (POST data)
	 *
	 * @access public
     * @param mixed $pks: array of items to be updated
 	 * @param mixed $post: updated form data
 	 * @return string  message to display as notification
	 */
	public function saveshort(&$pks, &$post)
	{
		// Reference global application object
		$date = Factory::getDate();
		$user = Factory::getUser();

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblRound                           = $this->getTable();
			$tblRound->id                       = $pks[$x];
			$tblRound->rien_ne_va_plus          = $post['rien_ne_va_plus' . $pks[$x]];
			$tblRound->points_tipp              = $post['points_tipp' . $pks[$x]];
			$tblRound->points_correct_result    = $post['points_correct_result' . $pks[$x]];
			$tblRound->points_correct_diff      = $post['points_correct_diff' . $pks[$x]];
			$tblRound->points_correct_draw      = $post['points_correct_draw' . $pks[$x]];
			$tblRound->points_correct_tendence  = $post['points_correct_tendence' . $pks[$x]];

			// Set the values
			$tblRound->modified    = $date->toSql();
			$tblRound->modified_by = $user->get('id');

			if (!$tblRound->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE');
	}

	/**
	 * Method to add predrounds from parent JSM Project
	 * New Items will be added unpublished and using default parameters
	 *
	 * @access public
     * @param array $predRoundsIdsToAdd: array of proj rounds t be added
 	 * @param int $prediction_id: id of parents prediction project
 	 * @param int $project_id: id of parents JS; project
 	 * @return string  message to display as notification
	 */
	public function addPredRoundIds($projRoundsIdsToAdd, $prediction_id, $project_id)
	{
		// Reference global application object
		$date = Factory::getDate();
		$user = Factory::getUser();

		$cnt = 0;

		foreach ($projRoundsIdsToAdd AS $projRoundsIdToAdd)
		{
			$tblRound                = $this->getTable();
			$tblRound->prediction_id = $prediction_id;
			$tblRound->project_id    = $project_id;
			$tblRound->round_id      = $projRoundsIdToAdd;

			// Set the values
			$tblRound->modified    = $date->toSql();
			$tblRound->modified_by = $user->get('id');
			$tblRound->published = 0;
			
			if (!$tblRound->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
			$cnt++;
		}
		return Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_ADDED', $cnt);
	}
}
