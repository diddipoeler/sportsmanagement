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

?>
<table>
  
<?php
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
echo '<td>';
echo $value->roundcode;

echo '</td>';    
  }
echo '</tr>';  



foreach ( $this->ranking as $rankkey => $rankvalue )
{  
echo '<tr>';
echo '<td>';
echo $rankkey;
echo '</td>';
echo '<td>';
echo $rankvalue['teamplayerid'];
echo '</td>';  

echo '<td>';
echo '</td>'; 
echo '<td>';
echo '</td>'; 
  
foreach ( $this->rounds as $key => $value )
  {
echo '<td>';
echo $rankvalue[$value->roundcode];
echo '</td>';
  }
echo '<td>';
echo $rankvalue['total'];
echo '</td>';   
echo '</tr>'; 
 } 
























  
?>
  </table>
