<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_matches
 * @file       match.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

?>
<div id="modJLML<?php echo $module->id . '_row' . $cnt; ?>" class="<?php echo $styleclass; ?> jlmlmatchholder">
    <!--jlml-mod<?php echo $module->id . 'nr' . $cnt; ?> start-->
	<?php
	if ($heading != $lastheading)
	{
		?>
        <div class="contentheading">
			<?php echo $heading; ?>
        </div>
		<?php
	}
	if ($show_pheading)
	{
		?>
        <div class="<?php echo $params->get('heading_style'); ?>">
			<?php echo $pheading; ?>
        </div>
		<?php
	}
	?>
    <table class="table">
        <tr class="<?php echo $styleclass; ?>">
            <td colspan="3">
				<?php
				if (!empty($match['location'])) echo '<span style="white-space:nowrap;">' . $match['location'] . '</span> ';
				echo ' <span style="white-space:nowrap;">' . $match['date'] . '</span> '
					. ' <span style="white-space:nowrap;">' . $match['time'] . ' Uhr</span> ';
				if (isset($match['meeting'])) echo ' <span style="white-space:nowrap;">' . $match['meeting'] . '</span> ';
				?>

            </td>
        </tr>
        <tr class="<?php echo $styleclass; ?>">
            <td class="jlmlteamcol">
				<?php
				if (!empty($match['hometeam']['logo']))
				{
					echo '<img src="' . $match['hometeam']['logo']['src'] . '" alt="' . $match['hometeam']['logo']['alt'] . '" title="' . $match['hometeam']['logo']['alt'] . '" ' . $match['hometeam']['logo']['append'] . ' />';
					?><br/>
					<?php
				}
				if ($params->get('show_names') == 1)
				{
					echo $match['hometeam']['name'];
				}
				if (!empty($match['homeover'])) echo $match['homeover'];
				?>
            </td>
            <td class="jlmlResults">
				<?php if ($match['cancel'] == 1)
				{
				?><span class="jsmlCancelR"><?php
					}
					else
					{
					?><span class="jlmlResults"><?php
						}
						?>

						<?php

						if ($match['resultpenalty'])
						{
							echo $match['resultpenalty'];
						}
                        elseif ($match['resultovertime'])
						{
							echo $match['resultovertime'];
						}
						else
						{
							echo $match['result'];
						}

						?>
      </span>
    <?php
    if ($match['reportlink'] OR $match['statisticlink'] OR $match['nextmatchlink'])
    { ?>
        <span class="jlmlMatchLinks">
    <?php
    if ($match['reportlink'])
    {
	    echo $match['reportlink'];
    }
    if ($match['statisticlink'])
    {
	    echo $match['statisticlink'];
    }
    if ($match['nextmatchlink'])
    {
	    echo $match['nextmatchlink'];
    }
    ?>
      </span>
    <?php } ?>
            </td>
            <td class="jlmlteamcol">
				<?php
				if (!empty($match['awayteam']['logo']))
				{
					echo '<img src="' . $match['awayteam']['logo']['src'] . '" alt="' . $match['awayteam']['logo']['alt'] . '" title="' . $match['awayteam']['logo']['alt'] . '" ' . $match['awayteam']['logo']['append'] . ' />';
					?><br/>
					<?php
				}
				if ($params->get('show_names') == 1)
				{
					echo $match['awayteam']['name'];
				}
				if (!empty($match['awayover'])) echo $match['awayover'];
				?>
            </td>
        </tr>
		<?php
		if (!empty($match['partresults']))
		{ ?>
            <tr class="<?php echo $styleclass; ?>">
                <td colspan="3"><?php echo $match['partresults']; ?>

                </td>
            </tr>
			<?php
		}
		?>
		<?php
		if (isset($match['referee']) OR isset($match['crowd']))
		{ ?>
            <tr class="<?php echo $styleclass; ?>">
                <td colspan="3">
					<?php
		 
		 //echo '<pre>'.print_r($match['referee'],true).'</pre>';
     $output = '';
     foreach( $match['referee'] as $key => $value )
     {
     $output .= '<span style="float:right;">';  
       //JPATH_COMPONENT.
       $output .= HTMLHelper::image(Uri::root().'modules/mod_sportsmanagement_matches/assets/images/colored/referee.png', Text::_($value->position_name), array(
					'title'  => Text::_($value->position_name),
					'height' => '16',
					'width'  => '16'
				)
			) ;
       
     $output .=  htmlspecialchars(sportsmanagementHelper::formatName(null, $value->firstname, $value->nickname, $value->lastname, $params->get("referee_name_format")), ENT_QUOTES, 'UTF-8').'</span><br>';
     }
		 $output .=  '<br>'.$match['spectators'];  
      echo $output;
		 
		 
					//echo $match['referee'] . ' ' . $match['spectators'];
					?>
                </td>
            </tr>
			<?php
		}
		if (!empty($match['notice']))
		{ ?>
            <tr class="<?php echo $styleclass; ?>">
                <td colspan="3">
					<?php
					echo $match['notice'];
					?>
                </td>
            </tr>

			<?php
		}
		?>
    </table>
	<?php
	if ($match['ajax']) echo $match['ajax'];
	$limit = (int) $params->get("limit");
	if ($limit > 1)
	{
		?>
        <hr style="width:100%;display:block;clear:both;margin-top:10px;"/>
	<?php } ?>
    <!--jlml-mod<?php echo $module->id . 'nr' . $cnt; ?> end-->
</div>
<?php
if ($ajax && $ajaxmod == $module->id)
{
	exit();
} ?>
