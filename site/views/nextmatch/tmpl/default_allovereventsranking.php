<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_allovereventsranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

//echo 'alloverevents <pre>'.print_r($this->alloverevents,true).'</pre>';
//echo 'overallevents <pre>'.print_r($this->overallevents,true).'</pre>';

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatchallovereventsranking">
<?php
unset($this->notes);
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_ALLOVEREVENTSRANKING');
echo $this->loadTemplate('jsm_notes'); 
?>
<div class="panel-group" id="allovereventsranking">
<?php
foreach ( $this->overallevents as $overallevents )
{
unset($ranking);
/** tabelle pro ereignis */  
foreach ( $this->alloverevents as $alloverevents => $value ) if ( $value->events[$overallevents->id]->event_sum != 0  )
{
$temp = new stdclass;  
$temp->playerid = $alloverevents;
$temp->event_sum = $value->events[$overallevents->id]->event_sum;  
$ranking[] = $temp; 
}
/** absteigend sortieren */
usort($ranking, function($a, $b) { return $b->event_sum - $a->event_sum; });  
$width    = 20;
$height   = 20;
$type     = 4;
$imgTitle = Text::_($overallevents->name);
$icon     = sportsmanagementHelper::getPictureThumb($overallevents->icon, $imgTitle, $width, $height, $type);
?>
<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#allovereventsranking"
                       href="#<?php echo $overallevents->name; ?>"><?php echo $icon.' '.Text::_($overallevents->name); ?></a>
                </h4>
            </div>
            <div id="<?php echo $overallevents->name; ?>" class="panel-collapse collapse">
                <div class="panel-body">
                    <table class="table <?php echo $this->config['table_class'] ?>">
						<?php
//echo 'ranking <pre>'.print_r($ranking,true).'</pre>'; 						
  
foreach ( $ranking as $rankingkey => $rankingvalue )
{  
?>
<tr>  
<td>
<?php
echo $this->alloverevents[$rankingvalue->playerid]->team_name;
?>
</td>
 
<td>
<?php
echo sportsmanagementHelper::formatName(null, $this->alloverevents[$rankingvalue->playerid]->firstname1, $this->alloverevents[$rankingvalue->playerid]->nickname1, $this->alloverevents[$rankingvalue->playerid]->lastname1, $this->config["name_format"]);
?>
</td>
  
<td>
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage(
											'nextmatchalloverevents' . $this->alloverevents[$rankingvalue->playerid]->playerid ,
											$this->alloverevents[$rankingvalue->playerid]->tppicture1,
											$this->alloverevents[$rankingvalue->playerid]->lastname1,
											'20',
											'',
											$this->modalwidth,
											$this->modalheight,
											$this->overallconfig['use_jquery_modal']
										);
?>
</td>
<td>
<?php
echo $rankingvalue->event_sum;
?>
</td>  
</tr> 
<?php  
  
}  
  
  
  
						?>
                    </table>
                </div>
            </div>
        </div>
<?php
}
?>
</div>
</div>