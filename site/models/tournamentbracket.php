<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       tournamentbracket.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Component\ComponentHelper;


class sportsmanagementModeltournamentbracket extends BaseDatabaseModel
{

function gettournamentbracket($project_id = 0)
  {
  $db    = $this->getDbo();
		$query = $db->getQuery(true);
echo $project_id;

  /**
		 *
		 * alle runden
		 */
		$query->clear();
		$query->select('*');
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . $project_id);
		$query->where('tournement = 1');
  $query->order('roundcode DESC');
		$db->setQuery($query);
		$roundresult = $db->loadObjectList('id');

  /**
		$roundresult2 = usort(
			$roundresult, function ($a, $b) {
			$c = $a->roundcode - $b->roundcode;

			return $c;
		}
		);
*/
		 Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult,true).'</pre>'  , '');
  //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult2,true).'</pre>'  , '');
  
  
  }

  
}

?>
