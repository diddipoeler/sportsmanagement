<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage scoresheet
 * @file       view.raw.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

JLoader::import('joomla.application.component.view');

class sportsmanagementViewScoresheet extends JViewLegacy
{

	public function display($tpl = null)
	{
		parent::display($tpl);
	}
}
