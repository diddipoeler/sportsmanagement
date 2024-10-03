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
 * https://github.com/simonbengtsson/jsPDF-AutoTable?tab=readme-ov-file
 * https://github.com/parallax/jsPDF
 *
 * https://github.com/simonbengtsson/jsPDF-AutoTable
 * 
 * 
 * https://stackoverflow.com/questions/58427634/php-html-table-as-excel-xls-file
 * 
 * excel
 * https://cdnjs.com/libraries/xlsx
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
$document = Factory::getDocument();
$document->addScript('https://unpkg.com/jspdf@2.5.2/dist/jspdf.umd.min.js'); // path to js script
$document->addScript('https://unpkg.com/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.js'); // path to js script		
$document->addScript('https://html2canvas.hertzen.com/dist/html2canvas.min.js'); // path to js script
		
// echo '<pre>'.print_r($this->jinput->getInt('p', 0),true).'</pre>';
$this->ranking = array();
      
$this->matchsingle = sportsmanagementModelMatch::getMatchAllSingleData($this->jinput->getInt("p", 0));
// echo '<pre>'.print_r($this->matchsingle,true).'</pre>';

$this->rounds = sportsmanagementModelProject::getRounds($ordering = 'ASC',0, false);
// echo '<pre>'.print_r($this->rounds,true).'</pre>';


foreach ( $this->matchsingle as $key => $value )
{
$this->ranking[$value->teamplayer1_id]['teamplayerid'] = $value->teamplayer1_id;
$this->ranking[$value->teamplayer1_id]['projectteamid'] = $value->projectteam1_id;
$this->ranking[$value->teamplayer2_id]['teamplayerid'] = $value->teamplayer2_id;
$this->ranking[$value->teamplayer2_id]['projectteamid'] = $value->projectteam2_id;
  
$gesamtresult = $value->team1_result + $value->team2_result;
//$playerresult1 = 0;
//$playerresult2 = 0;  

  /**
  if ( $gesamtresult > 2 )
    {
      $playerresult1++;
      $playerresult2++;
    }
*/



  /**
  if ( $value->team1_result > $value->team2_result )
    {
      $playerresult1 = $playerresult1 + 2;
      $playerresult2 = $playerresult2 + 0;
      $this->ranking[$value->teamplayer1_id]['G'] += $value->team1_result;
      $this->ranking[$value->teamplayer1_id]['V'] += $value->team2_result;
      
      $this->ranking[$value->teamplayer2_id]['V'] += $value->team2_result;
      $this->ranking[$value->teamplayer2_id]['G'] += $value->team1_result;
    }
  elseif ( $value->team1_result < $value->team2_result )
    {
      $playerresult1 = $playerresult1 + 0;
      $playerresult2 = $playerresult2 + 2;
      
      $this->ranking[$value->teamplayer2_id]['G'] += $value->team2_result;
      $this->ranking[$value->teamplayer2_id]['V'] += $value->team1_result;
      
      $this->ranking[$value->teamplayer1_id]['G'] += $value->team1_result;
      $this->ranking[$value->teamplayer1_id]['V'] += $value->team2_result;
      
    }
*/
  
//if ( $value->team1_result > $value->team2_result && $gesamtresult > 2 )
  if ( $value->team1_result > $value->team2_result  )
    {
$this->ranking[$value->teamplayer1_id][$value->roundcode]['G'] += 1;
$this->ranking[$value->teamplayer1_id]['totalG'] += 1;      
      }

//if ( $value->team1_result < $value->team2_result && $gesamtresult > 2 )
  if ( $value->team1_result < $value->team2_result  )
    {
$this->ranking[$value->teamplayer2_id][$value->roundcode]['G'] += 1;
$this->ranking[$value->teamplayer2_id]['totalG'] += 1; 
  
}
  
$this->ranking[$value->teamplayer1_id][$value->roundcode]['G'] += $value->team1_result;
$this->ranking[$value->teamplayer1_id][$value->roundcode]['V'] = $value->team2_result;  
$this->ranking[$value->teamplayer1_id]['total'] += $playerresult1;
$this->ranking[$value->teamplayer1_id]['totalG'] += $value->team1_result;  
$this->ranking[$value->teamplayer1_id]['totalV'] += $value->team2_result;     

$this->ranking[$value->teamplayer2_id][$value->roundcode]['G'] += $value->team2_result;
$this->ranking[$value->teamplayer2_id][$value->roundcode]['V'] = $value->team1_result;  
$this->ranking[$value->teamplayer2_id]['total'] += $playerresult2;    
$this->ranking[$value->teamplayer2_id]['totalG'] += $value->team2_result;    
$this->ranking[$value->teamplayer2_id]['totalV'] += $value->team1_result;
  
}

$volume  = array_column($this->ranking, 'totalG');
$volume2  = array_column($this->ranking, 'totalV');      
// Sort the data with volume descending, edition ascending
// Add $data as the last parameter, to sort by the common key
array_multisort($volume, SORT_DESC,  $volume2, SORT_ASC,  $this->ranking);
      
// echo '<pre>'.print_r($this->ranking,true).'</pre>';

		
  }

}
