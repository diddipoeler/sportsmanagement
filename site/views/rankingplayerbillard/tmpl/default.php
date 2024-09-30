<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingplayerbillard
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

echo $this->project->name;
?>
<div class="row table-responsive">
<table class="table table-striped">
  
<?php
  echo '<tr>';

echo '<td>';
echo 'Rank';
echo '</td>';    
echo '<td>';
echo 'Player';
echo '</td>';    
  echo '<td>';
echo 'Team';
echo '</td>';    
  echo '<td>';
echo '';
echo '</td>';    

  
foreach ( $this->rounds as $key => $value )
  {
echo '<td colspan="2">';
echo $value->roundcode;

echo '</td>';    
  }
  /**
echo '<td>';
echo 'Total';

echo '</td>';
  */
echo '<td>';
echo 'G';

echo '</td>';
echo '<td>';
echo 'V';

echo '</td>';
  
echo '</tr>';  

  /** datum der runde */
echo '<tr>';
echo '<td>';

echo '</td>';    
echo '<td>';

echo '</td>';    
  echo '<td>';

echo '</td>';  
 echo '<td>';

echo '</td>';
  foreach ( $this->rounds as $key => $value )
  {
echo '<td colspan="2" nowrap>';
echo date( "d-m", strtotime($value->round_date_first)) ;

echo '</td>';        
  }
  
  
echo '</tr>';  


/** g/v der runde */  
echo '<tr>';
echo '<td>';

echo '</td>';    
echo '<td>';

echo '</td>';    
  echo '<td>';

echo '</td>';    
  echo '<td>';

foreach ( $this->rounds as $key => $value )
  {
echo '<td>';
echo 'G';
echo '</td>';    
echo '<td>';
echo 'V';
echo '</td>';        
  }


  
echo '</td>';    




echo '</tr>'; 



  
foreach ( $this->ranking as $rankkey => $rankvalue )
{  
echo '<tr>';
echo '<td>';
  $platz = $rankkey + 1;
echo $platz;
echo '</td>';
echo '<td nowrap>';
//echo $rankvalue['teamplayerid'];
$playerinfo = sportsmanagementModelPlayer::getTeamPlayer($this->project->id, 0, $rankvalue['teamplayerid']);  
 //echo '<pre>'.print_r($playerinfo,true).'</pre>';  

  foreach ($playerinfo as $player)
					{
//echo $player->firstname . ' ' . $player->lastname.' ('.$player->knvbnr.')';
echo $player->firstname . ' ' . $player->lastname;                      
                      }
echo '</td>';  

echo '<td nowrap>';
$teaminfo = sportsmanagementModelProject::getTeaminfo($rankvalue['projectteamid'], 0);
//echo '<pre>'.print_r($teaminfo,true).'</pre>';  
  echo $teaminfo->name;
echo '</td>'; 
echo '<td>';
echo $player->knvbnr;
  
echo '</td>'; 
  
foreach ( $this->rounds as $key => $value )
  {
echo '<td>';
echo $rankvalue[$value->roundcode]['G'];
echo '</td>';

    echo '<td>';
echo $rankvalue[$value->roundcode]['V'];
    echo '</td>';
  }
  /**
echo '<td>';
echo $rankvalue['total'];
echo '</td>';
*/
echo '<td>';
echo $rankvalue['totalG'];
echo '</td>';

echo '<td>';
echo $rankvalue['totalV'];
echo '</td>';

   
echo '</tr>'; 
 } 
























  
?>
  </table>
</div>
