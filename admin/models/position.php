<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       position.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * sportsmanagementModelposition
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelposition extends JSMModelAdmin
{

	/**
	 * Method to update checked positions
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$app = Factory::getApplication();

		// Get the input
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$post = Factory::getApplication()->input->post->getArray(array());

			 $result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblPosition = & $this->getTable();
			$tblPosition->id = $pks[$x];
			$tblPosition->parent_id    = $post['parent_id' . $pks[$x]];

			if (!$tblPosition->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result = false;
			}
		}

		return $result;
	}



}
