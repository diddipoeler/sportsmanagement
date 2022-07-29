<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.1.0
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_randomplayer
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


	/** Check if any player returned */
	if ( array_key_exists('player', $list) )
		{
		$items = 1;
		}
		else
		{
		$items = 0;    
		}

	if (!$items)
		{
			echo '<p class="modjlgrandomplayer">' . Text::_('NO ITEMS') . '</p>';
			return;
		} 


	$mode = $params->get('mode');

	switch ($mode)
	{
		/**
		*
		*    Sticker template
		*/
	
	case 'S':

		$person                               = $list['player'];
		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
		$routeparameter['s']                  = $params->get('s');
		$routeparameter['p']                  = $list['project']->slug;
		$routeparameter['tid']                = $list['infoteam']->team_slug;
		$routeparameter['pid']                = $person->slug;
		$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
		$project_name						  = $list['project']->name;
		
		$picturetext = Text::_('MOD_SPORTSMANAGEMENT_RANDOMPLAYER_PERSON_PICTURE');
		$text        = sportsmanagementHelper::formatName( null, $person->firstname, $person->nickname, $person->lastname,	$params->get("name_format")	);
		
		$imgTitle = Text::sprintf($picturetext . ' %1$s', $text);

		if (isset($list['inprojectinfo']->picture))
		{
			$picture = $list['inprojectinfo']->picture;
		}
		else
		{
			$picture = '';
		}

		$pic = sportsmanagementHelper::getPictureThumb($picture, $imgTitle, $params->get('picture_width'), 'auto');

        $flag = JSMCountries::getCountryFlag($person->country);
		$flag_url = get_string_between($flag, '"', '"'); 
		$border_color = $params->get('border_color');
		$inside_color = $params->get('inside_color');
		$text_color = $params->get('text_color');
         
?>		
		<div>
		<div class="container-fluid" style="border: 2px solid <? echo $border_color;?>; 
											background-color: <? echo $inside_color;?>; 
											border-radius: 20px;
											width:250px; 
											height: 350px;
											display: flex;
											box-shadow: 10px 10px 6px 3px #474747;
											margin: 0px 0px 30px 0px;
											">
											
				<div  style="border: 1px solid <? echo $border_color;?>; 
											background-color: #eee;
											border-radius: 20px;
											width:180px; 
											height: 230px;
											background-image: url(<?php echo $picture; ?>);
								   		    background-size: contain;
											position: absolute;
											background-position: center;
											background-repeat: no-repeat;
											margin: 30px 0px 0px 5px;
											">
											
											
						

				</div>
		
			<p style="color: <? echo $text_color;?> ; 
				    font-family: sans-serif;
					width:180px; 
					font-size: 18px; 
					margin: 0px 0px 0px 15px;
									">	<?php echo $text ?> </p>
		
		
		    <p style="color: <? echo $text_color;?>; 
				      font-family: sans-serif; 
					font-size: 18px;
					position: absolute;
					margin: 300px 0px 0px 5px;
									">	<?php echo $list['project']->name; ?> </p>						
									
									
			<p style="color: <? echo $text_color;?>; 
				      font-family: sans-serif; 
					font-size: 20px;
					position: absolute;
					margin: 260px 0px 0px 5px;
			
									">	<?php echo $list['inprojectinfo']->position_name; ?> </p>						
									
									
            <p style="color: <? echo $text_color;?>; 
				      font-family: sans-serif; 
					font-size: 20px;
					position: absolute;
					margin: 130px 0px 0px -20px;
					writing-mode: tb-rl;
					transform: rotate(-180deg);
			
									">	<?php echo $list['infoteam']->name; ?> </p>	

			<div  style="	background-color: #eee;
							border-radius: 30px;
							width:30px; 
							height: 30px;
							background-image: url(<?php echo $flag_url; ?>);
						    background-size: contain;
							position: absolute;
							background-position: center;
							background-repeat: no-repeat;
							margin: 240px 0px 0px 160px;
							">
									
		
		</div>
		
								
		
		</div>
      
	<?php 
	break;

	/**
	 *
	 * Plain template
	 */
	case 'P':
	
	
		?>

	<div class="container-fluid" >
		<div class="col-md-10 blogShort">
			<?php if ($params->get('show_project_name')) :
				?>	

				<h4><?php echo $list['project']->name; ?></h4>

			<?php endif; ?> <?php
			$person                               = $list['player'];
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
			$routeparameter['s']                  = $params->get('s');
			$routeparameter['p']                  = $list['project']->slug;
			$routeparameter['tid']                = $list['infoteam']->team_slug;
			$routeparameter['pid']                = $person->slug;
			$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

		// $link = sportsmanagementHelperRoute::getPlayerRoute( $list['project']->slug,
		//												$list['infoteam']->team_id,
		//												$person->slug );
		?>

		<?php
		$picturetext = Text::_('MOD_SPORTSMANAGEMENT_RANDOMPLAYER_PERSON_PICTURE');
		$text        = sportsmanagementHelper::formatName(
			null, $person->firstname,
			$person->nickname,
			$person->lastname,
			$params->get("name_format")
		);

		$imgTitle = Text::sprintf($picturetext . ' %1$s', $text);

		if (isset($list['inprojectinfo']->picture))
		{
			$picture = $list['inprojectinfo']->picture;
		}
		else
		{
			$picture = '';
		}

		$pic = sportsmanagementHelper::getPictureThumb($picture, $imgTitle, $params->get('picture_width'), 'auto');
		echo '<a href="' . $link . '">' . $pic . '</a>';
		?>

        <article>
            <p>
				<?php
				if ($params->get('show_player_flag'))
				{
					echo JSMCountries::getCountryFlag($person->country) . " ";
				}


				if ($params->get('show_player_link'))
				{
					$routeparameter                       = array();
					$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
					$routeparameter['s']                  = $params->get('s');
					$routeparameter['p']                  = $list['project']->slug;
					$routeparameter['tid']                = $list['infoteam']->team_slug;
					$routeparameter['pid']                = $person->slug;
					$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

					//		$link = sportsmanagementHelperRoute::getPlayerRoute($list['project']->slug,
					//														$list['infoteam']->team_id,
					//														$person->slug );
					echo HTMLHelper::link($link, $text);
				}
				else
				{
					echo Text::sprintf('%1$s', $text);
				}
				?>
            </p>
			<?php if ($params->get('show_team_name'))
				:
				?>
                <p>
					<?php
					echo sportsmanagementHelper::getPictureThumb(
							$list['infoteam']->team_picture,
							$list['infoteam']->name,
							$params->get('team_picture_width', 21),
							'auto',
							1
						) . " ";
					$text = $list['infoteam']->name;

					if ($params->get('show_team_link'))
					{
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
						$routeparameter['s']                  = $params->get('s');
						$routeparameter['p']                  = $list['project']->slug;
						$routeparameter['tid']                = $list['infoteam']->team_slug;
						$routeparameter['ptid']               = 0;
						$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);

						//		$link = sportsmanagementHelperRoute::getTeamInfoRoute($list['project']->slug,
						//														$list['infoteam']->team_id);
						echo HTMLHelper::link($link, $text);
					}
					else
					{
						echo Text::sprintf('%1$s', $text);
					}
					?>
                </p>
			<?php endif; ?>
			<?php
			if ($params->get('show_position_name') && isset($list['inprojectinfo']->position_name))
				:
				?>
                <p>
					<?php
					$positionName = $list['inprojectinfo']->position_name;
					echo Text::_($positionName); ?>
                </p>
			<?php endif; ?>
        </article>
    </div>

    
	<?PHP
	break; 

	}







/*
   extract substring 
*/

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

?>