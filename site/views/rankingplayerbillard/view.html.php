<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingplayerbillard
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Form;

/**
 * sportsmanagementViewMatchReport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewrankingplayerbillard extends sportsmanagementView
{

	
	public function init()
	{
// echo '<pre>'.print_r($this->jinput->getInt('p', 0),true).'</pre>';
$ranking = array();
      
$this->matchsingle = sportsmanagementModelMatch::getMatchAllSingleData($this->jinput->getInt("p", 0));
 echo '<pre>'.print_r($this->matchsingle,true).'</pre>';

$this->rounds = sportsmanagementModelProject::getRounds($ordering = 'ASC',0, false);
 echo '<pre>'.print_r($this->rounds,true).'</pre>';


foreach ( $this->matchsingle as $key => $value )
  {
$ranking[$value->teamplayer1_id][$value->roundcode] = $value->team1_result;
$ranking[$value->teamplayer1_id]['total'] += $value->team1_result;
    
$ranking[$value->teamplayer2_id][$value->roundcode] = $value->team2_result;
$ranking[$value->teamplayer2_id]['total'] += $value->team2_result;    
    
  }

echo '<pre>'.print_r($ranking,true).'</pre>';

		
  }

}
