<?php	
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_players_card.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage roster
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

foreach ( $this->rows as $position_id => $players ): ?>
<div style="margin:auto; width:100%;">
	<!-- position header -->
	<?php 
		$row = current($players);
		$position	= $row->position;
		$k			= 0;
		$colspan	= ( ( $this->config['show_birthday'] > 0 ) ? '6' : '5' );	?>
<h2><?php	echo '&nbsp;' . Text::_( $row->position );	?></h2>
<?php 

foreach ($players as $row): ?>
<tr	class="">
<div class="mini-player_links">
			<table class="table">
			  <tbody><tr>
			    <td>	       
			         <div class="player-trikot">		
					<?php
					if ( ! empty( $row->position_number ) )
					{  
						echo $row->position_number;
					} 
					?>	 
					</div>		       
			     </td>
			    <td style="width: 55px;padding:0px;">		      
				<?php
				$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
				$imgTitle = Text::sprintf( $playerName );
				$picture = $row->picture;
				if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
				{
					$picture = $row->ppic;
				}
				if ( !curl_init( $picture ) )
				{
					$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
				}
				
echo sportsmanagementHelperHtml::getBootstrapModalImage('rosterplayer'.$row->person_id,
$picture,
$playerName,
$this->config['player_picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);				

				?>			  
				</td>
			    <td style="padding-left: 9px;">
			      <div class="player-position"><?php	echo Text::_( $row->position );	?></div>
				  <div class="player-name">
				  <?php 
				  	if ($this->config['link_player']==1)
					{
					   $routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $this->team->slug;
$routeparameter['pid'] = $row->person_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);

						echo HTMLHelper::link($link,'<i>'.$playerName.'</i>');
					}
					else
					{
						echo '<i>'.$playerName.'</i>';
					}		  
				  ?></b></a></div>
				  
				</td>
			  </tr>
			</tbody></table>
			</div>
			</tr>
			
			<?php	$k = 1 - $k; ?>
	<?php endforeach; ?>
	<div class="clear">
    </div>
    </div>
	<?php endforeach;	?>
