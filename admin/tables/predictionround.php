<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    3.8.20
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       predictionround.php
 * @author     jst, diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2020 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementTablePredictionRound
 *
 * @package
 * @author
 * @copyright jst
 * @version   2020
 * @access    public
 */
class sportsmanagementTablePredictionRound extends JSMTable
{
	/**
	 * Constructor
	 *
	 * @param   object Database connector object
	 *
	 * @since 1.0
	 */
	function __construct(&$db)
	{
		$db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_prediction_tippround', 'id', $db);
	}

}
