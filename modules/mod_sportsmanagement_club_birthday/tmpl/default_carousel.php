<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_club_birthday
 * @file       default_carousel.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * https://getbootstrap.com/docs/4.3/components/carousel/
 * 
 * https://getbootstrap.com/docs/5.2/components/carousel/
 * 
 * https://www.w3schools.com/howto/howto_js_slideshow.asp
 * https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_slideshow_auto
 * 
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
// images/logo-100.png
?>
<style>
* {box-sizing: border-box}
body {font-family: Verdana, sans-serif; margin:0}
.mySlides {display: none}
img {vertical-align: middle;}

/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: auto;
}

/* Next & previous buttons */
.prev, .next {
  cursor: pointer;
  position: absolute;
  top: 50%;
  width: auto;
  padding: 16px;
  margin-top: -22px;
  color: white;
  font-weight: bold;
  font-size: 18px;
  transition: 0.6s ease;
  border-radius: 0 3px 3px 0;
  user-select: none;
}

/* Position the "next button" to the right */
.next {
  right: 0;
  border-radius: 3px 0 0 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover, .next:hover {
  background-color: rgba(0,0,0,0.8);
}

/* Caption text */
.text {
  color: #070000;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: 8px;
  width: 100%;
  text-align: center;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #070000;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* The dots/bullets/indicators */
.dot {
  cursor: pointer;
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active, .dot:hover {
  background-color: #717171;
}

/* Fading animation */
.fade {
  animation-name: fade;
  animation-duration: 1.5s;
}

@keyframes fade {
  from {opacity: .4} 
  to {opacity: 1}
}

/* On smaller screens, decrease text size */
@media only screen and (max-width: 300px) {
  .prev, .next,.text {font-size: 11px}
}
</style>

<!-- Slideshow container -->
<div class="slideshow-container">


<?PHP
//echo '<pre>'.print_r($clubs,true).'</pre>';

foreach ($clubs AS $club)
						{
if ( !$club->founded_timestamp )
{
$club->founded_timestamp = strtotime($club->date_of_birth);
}
            }

//echo '<pre>'.print_r($clubs,true).'</pre>'; 
/**
usort($clubs, function($a, $b) {
      if ($a->founded_timestamp == $b->founded_timestamp) {
        return 0;
    }
    return ($a->founded_timestamp < $b->founded_timestamp) ? -1 : 1;
});
*/

// Asc sort
usort($clubs,function($first,$second){
    return $first->daymonth > $second->daymonth;
});



//echo '<pre>'.print_r($clubs,true).'</pre>'; 	
	
	
	
	
	
	
	
						$a = 1;
$gesamtclubs = count($clubs);
						foreach ($clubs AS $club)
						{
							// $thispic = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
							$thispic = $module->picture_server . $club->picture;
							$active  = ($a == 0) ? 'active' : '';

							$club->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');

							if ($params->get('show_picture'))
							{
								/*
								if ( curl_init($club->picture) && $club->picture != '' )
								{
								$thispic = $club->picture;
								}
								elseif( curl_init($club->default_picture) && $club->default_picture != '' )
								{
								$thispic = $club->default_picture;
								}
								*/
							}

							switch ($club->days_to_birthday)
							{
								case 0:
									$whenmessage = $params->get('todaymessage');

									break;
								case 1:
									$whenmessage = $params->get('tomorrowmessage');

									break;
								default:
									$whenmessage = str_replace('%DAYS_TO%', $club->days_to_birthday, trim($futuremessage));

									break;
							}

							if ($club->founded != '0000-00-00')
							{
								$birthdaytext2  = htmlentities(trim(Text::_($params->get('birthdaytext'))), ENT_COMPAT, 'UTF-8');
								$dayformat      = htmlentities(trim($params->get('dayformat')));
								$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
								$birthdaytext2  = str_replace('%WHEN%', $whenmessage, $birthdaytext2);
								$birthdaytext2  = str_replace('%AGE%', $club->age, $birthdaytext2);
								$birthdaytext2  = str_replace('%DATE%', date($dayformat, strtotime($club->year . '-' . $club->daymonth)), $birthdaytext2);
								$birthdaytext2  = str_replace('%DATE_OF_BIRTH%', date($birthdayformat, strtotime($club->date_of_birth)), $birthdaytext2);
							}
							else
							{
								$birthdaytext2 = htmlentities(trim(Text::_($params->get('birthdaytextyear'))), ENT_COMPAT, 'UTF-8');
								$birthdaytext2 = str_replace('%AGE%', $club->age_year, $birthdaytext2);
							}

							$birthdaytext2 = str_replace('%BR%', '<br />', $birthdaytext2);
							$birthdaytext2 = str_replace('%BOLD%', '<b>', $birthdaytext2);
							$birthdaytext2 = str_replace('%BOLDEND%', '</b>', $birthdaytext2);
$text = $club->name.'<br>';
$text .= $birthdaytext2.'<br>';
$flag     = $params->get('show_club_flag') ? JSMCountries::getCountryFlag($club->country) . "&nbsp;" : "";
//$text     = $club->name;
//$usedname = $flag . $text;
$text .= $flag.'<br>';
							?>
           
    <div class="mySlides fade">
    <div class="numbertext"><?php echo $a; ?> / <?php echo $gesamtclubs; ?></div>
    <img src="<?php echo $thispic; ?>" style="width:15%">
    <div class="text"><?php echo $text; ?></div>
  </div>
                             

                               
                            
							<?PHP
							$a++;
						}
            //echo ' a <pre>'.print_r($a,true).'</pre>';
						?>



  <!-- Full-width images with number and caption text -->
  
  <!--
  <div class="mySlides fade">
    <div class="numbertext">1 / 3</div>
    <img src="images/logo-100.png" style="width:30%">
    <div class="text">Caption Text</div>
  </div>

  <div class="mySlides fade">
    <div class="numbertext">2 / 3</div>
    <img src="images/logo-100.png" style="width:30%">
    <div class="text">Caption Two</div>
  </div>

  <div class="mySlides fade">
    <div class="numbertext">3 / 3</div>
    <img src="images/logo-100.png" style="width:30%">
    <div class="text">Caption Three</div>
  </div>
-->
  <!-- Next and previous buttons -->
  <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
  <a class="next" onclick="plusSlides(1)">&#10095;</a>
</div>
<br>

<!-- The dots/circles -->
<div style="text-align:center">
<?php
for ($b=1; $b < $a;$b++)
{
?>
<!--  <span class="dot" onclick="currentSlide(1)"></span> -->
<!--   <span class="dot" onclick="currentSlide(2)"></span> -->
  <span class="dot" onclick="currentSlide(<?php echo $b; ?>)"></span>
<?php
}
?>  
</div>

<script>
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}    
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
  setTimeout(showSlides, 3000); // Change image every 2 seconds
}
</script>
