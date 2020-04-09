<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_count_rekord
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * modJSMStatistikRekordHelper
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMStatistikRekordHelper
{

	/**
	 * modJSMStatistikRekordHelper::getData()
	 *
	 * @param   mixed  $params
	 * @param   mixed  $module
	 *
	 * @return
	 */
	static function getData($params, $module)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);
		$result = array();

		if ($params->get('jsm_stat_spielpaarungen'))
		{
			$query->select('count(*) as total');
			$query->from('#__sportsmanagement_match');

			$db->setQuery($query);
			$anzahl = $db->loadResult();

			$temp             = new stdClass;
			$temp->image      = 'modules/' . $module->module . '/images/matches.png';
			$temp->anzahl     = $anzahl;
			$temp->anzahlbis  = $params->get('jsm_stat_paarungen');
			$temp->anzahldiff = $params->get('jsm_stat_paarungen') - $anzahl;
			$temp->text       = Text::sprintf('SHOW_MATCHES_DIFF', "<strong>" . number_format($temp->anzahldiff, 0, ",", ".") . "</strong>", "<strong>" . number_format($temp->anzahlbis, 0, ",", ".") . "</strong>");

			$result[] = $temp;
			$result   = array_merge($result);
			unset($temp);
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;

	}

}
