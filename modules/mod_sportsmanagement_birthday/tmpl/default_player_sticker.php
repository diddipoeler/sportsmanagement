<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.1.1
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_birthday
 * @file       default_play_card.php
 * @author     diddipoeler (diddipoeler@gmx.de), stony, svdoldie und donclumsy , llambion
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

?>

<style>
.text_rotate {  
    -webkit-transform: rotate(90deg); 
    -moz-transform: rotate(90deg); 
    -ms-transform: rotate(90deg); 
    -o-transform: rotate(90deg); 
    filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3); 
    transform: rotate(90deg);
    //display:block; 
    transform-origin: 0% 50%;
}

.vertical{
     transform: rotate(180deg);
     writing-mode: vertical-lr;
     text-align: center;
  color: #000000;
}  
  
  
</style>
<!--<link href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" rel="stylesheet">-->

<?php

if (count($persons) > 0)
{   

	foreach ($persons AS $person)
	{
		$text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');
		switch ($person['days_to_birthday'])
		{
			case 0:
				$whenmessage = $params->get('todaymessage');
				$today = 1 ;
				break;
			case 1:
				$whenmessage = $params->get('tomorrowmessage');
				$today = 0 ;
				break;
			default:
				$whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));
				$today = 0 ;
				break;
		}
		$birthdaytext   = htmlentities(trim(Text::_($params->get('birthdaytext'))), ENT_COMPAT, 'UTF-8');
		$dayformat      = htmlentities(trim($params->get('dayformat')));
		$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
		$birthdaytext   = str_replace('%WHEN%', $whenmessage, $birthdaytext);
		$birthdaytext   = str_replace('%AGE%', $person['age'], $birthdaytext);
		$birthdaytext   = str_replace('%DATE%', strftime($dayformat, strtotime($person['year'] . '-' . $person['daymonth'])), $birthdaytext);
		$birthdaytext   = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($person['date_of_birth'])), $birthdaytext);
		$birthdaytext   = str_replace('%BR%', '<br />', $birthdaytext);
		$birthdaytext   = str_replace('%BOLD%', '<b>', $birthdaytext);
		$birthdaytext   = str_replace('%BOLDEND%', '</b>', $birthdaytext);
		$person_link    = "";
		$person_type    = $person['type'];
		if ($person_type == 1)
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
			$routeparameter['s']                  = $person['season_slug'];
			$routeparameter['p']                  = $person['project_slug'];
			$routeparameter['tid']                = $person['team_slug'];
			$routeparameter['pid']                = $person['person_slug'];
			$person_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
		}
		else if ($person_type == 2)
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
			$routeparameter['s']                  = $person['season_slug'];
			$routeparameter['p']                  = $person['project_slug'];
			$routeparameter['tid']                = $person['team_slug'];
			$routeparameter['pid']                = $person['person_slug'];
			$person_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
		}
		else if ($person_type == 3)
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
			$routeparameter['s']                  = $person['season_slug'];
			$routeparameter['p']                  = $person['project_slug'];
			$routeparameter['pid']                = $person['person_slug'];
			$person_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('referee', $routeparameter);
		}

		$text           = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');
		$flag = JSMCountries::getCountryFlag($person['country']);
		$flag_url = get_string_between2($flag, '"', '"');
        $usedname       = $flag . $text;
		$params_com     = ComponentHelper::getParams('com_sportsmanagement');
		$usefontawesome = $params_com->get('use_fontawesome');        
		$showname = HTMLHelper::link($person_link, $usedname);	
		$picture = $person['picture'];
	
		$border_color = $params->get('border_color');
		$background_color = $params->get('background_color');
		$text_color = $params->get('text_color');
		$text_size = $params->get('text_size');
		$title_size = $params->get('title_size');
		$title_color = $params->get('title_color');
		$birthday_cake = $params->get('birthday_cake');
		$cake_image = $params->get('cake_image');
		$cake_image = SubStr($cake_image->imagefile,0,Strpos($cake_image->imagefile,'#'));
		$border = $params->get('border');
		$border_rounded = $params->get('border_rounded');
		$border_shadow = $params->get('border_shadow');
		$show_team = $params->get('show_team');
		$show_project = $params->get('show_project');
		
		
		$style = "";
		
		if ($border)
		 {
			$style = "border: 1px solid " . $border_color . "; " ;
			$style = $style . "background-color: " . $background_color . "; " ;
			if($border_rounded)
			{
				$style =  $style . 'border-radius: 20px ; ';
			}
			if($border_shadow)
			{
				$style =  $style . 'box-shadow: 10px 10px 6px 3px #474747; ';
			}			
			
		 }	

		//$style = $style . 'width:250px; ';
		$style = $style . 'margin: 0px 0px 30px 0px;';

	
	?>
		

<div class="container" style="<?php echo $style;?>" >
<div class="row">
<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 vertical" style="">

<?php echo $person['team_name'] ?>  
  
  
</div>  

<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10" style="">

  
<div class="short-div d-flex justify-content-center" style="">
   <p style="color: <?php echo $text_color;?> ; 
				    font-family: sans-serif;
					width:180px; 
					font-size: 18px; 
					margin: 0px 0px 0px 15px;
									">	<?php echo $text ?> </p> 
  </div>
  
<div class="short-div d-flex justify-content-center" style="">
   <picture>
             <img src="<?php echo $picture; ?>" class="img-fluid" alt="a" width="180px"/>
          </picture>
  </div>  
  
  <div class="short-div d-flex justify-content-center  text-dark" style="">
  
    <?php echo Text::_($person['position_name']) ?>
  </div>
  
<div class="short-div d-flex justify-content-center  text-dark" style="">
  <?php echo $birthdaytext ?>
  </div>  
  
</div>  
  
  
  
  
<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 vertical" style="">
 <img src="<?php echo $flag_url; ?>"  >
                  <?php echo SubStr($person['project_slug'],strpos($person['project_slug'],":")+1); ?>
  
  
  
</div>    
  
  
  
  
  
  
  
  
  
  
  
</div>
</div>



















	
<?php	
	}
}
	else
		{
		?>	

            <tr>
                    <td class="birthday">
                        <div class="bg-warning alert alert-warning">
							<?php echo '' . str_replace('%DAYS%', $params->get('maxdays'), htmlentities(trim(Text::_($params->get('not_found_text'))))) . ''; ?>
                        </div>
                    </td>
                </tr>
		<?php
		}


function get_string_between2($string, $start, $end)
	{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
	}


?>
