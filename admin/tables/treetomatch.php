<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       treetomatch.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementTableTreetoMatch
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementTableTreetoMatch extends JSMTable
{
	/**
	 * Constructor
	 *
	 * @param   object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		$db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_treeto_match', 'id', $db);
	}
}
