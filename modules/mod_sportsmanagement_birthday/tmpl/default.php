<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_birthday
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * https://getbootstrap.com/docs/4.0/components/carousel/
 * 
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

switch ($mode)
{
	/** bootstrap mode template */
	case 'B':
    if ( $params->def("load_bootstrap") )
    {
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">	
<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>	    
<?php
    }
		?>
        
<div class="row">
<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators">
  <?php
  $a = 0;
foreach ($persons AS $person)
{
    $active = ($a == 0) ? 'active' : '';
  ?>
    <li data-target="#carouselExampleIndicators" data-slide-to="<?php echo $a; ?>" class="<?php echo $active; ?>"></li>
    <?php
    $a++;
}    
    ?>
  </ol>
  <div class="carousel-inner">
    <?php
    $a = 0;
foreach ($persons AS $person)
{
$text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');

						$active = ($a == 0) ? 'active' : '';

						if ($params->get('show_picture'))
						{
							if (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $person['picture']) && $person['picture'] != '')
							{
								$thispic = $module->picture_server . DIRECTORY_SEPARATOR . $person['picture'];
							}
                            elseif (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $person['default_picture']) && $person['default_picture'] != '')
							{
								$thispic = $module->picture_server . DIRECTORY_SEPARATOR . $person['default_picture'];
							}
						}

						switch ($person['days_to_birthday'])
						{
							case 0:
								$whenmessage = $params->get('todaymessage');
								break;
							case 1:
								$whenmessage = $params->get('tomorrowmessage');
								break;
							default:
								$whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));
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
    
    ?>
    
    <div class="carousel-item <?php echo $active; ?>">
      <img class="d-block w-<?php echo $params->get('picture_width'); ?>" src="<?php echo $thispic; ?>" width="<?php echo $params->get('picture_width'); ?>" height="auto" alt="<?php echo $text; ?>" />
    <div class="carousel-caption d-none d-md-block">
    <h5><?php echo $text; ?></h5>
    <p><?php echo $birthdaytext; ?></p>
  </div>
    
    
    </div>
    
    <?php
    $a++;
    }
    ?>
  </div>
  
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
  
</div>

</div>        
        

		<?PHP
		break;

	default:
		?>

        <table class="table">
			<?php
			if (count($persons) > 0)
			{
				foreach ($persons AS $person)
				{
					if (($params->get('limit') > 0) && ($counter == intval($params->get('limit'))))
					{
						break;
					}

					$class = ($k == 0) ? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');

					$thispic  = "";
					$flag     = $params->get('show_player_flag') ? JSMCountries::getCountryFlag($person['country']) . "&nbsp;" : "";
					$text     = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], $person['nickname'], $person['lastname'], $params->get("name_format")), ENT_QUOTES, 'UTF-8');
					$usedname = $flag . $text;

					$person_link = "";
					$person_type = $person['type'];

					if ($person_type == 1)
					{
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
						$routeparameter['s']                  = $params->get("cfg_which_database");
						$routeparameter['p']                  = $person['project_slug'];
						$routeparameter['tid']                = $person['team_slug'];
						$routeparameter['pid']                = $person['person_slug'];
						$person_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
					}
                    elseif ($person_type == 2)
					{
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
						$routeparameter['s']                  = $params->get("cfg_which_database");
						$routeparameter['p']                  = $person['project_slug'];
						$routeparameter['tid']                = $person['team_slug'];
						$routeparameter['pid']                = $person['person_slug'];
						$person_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);
					}
                    elseif ($person_type == 3)
					{
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = $params->get("cfg_which_database");
						$routeparameter['s']                  = $params->get("cfg_which_database");
						$routeparameter['p']                  = $person['project_slug'];
						$routeparameter['pid']                = $person['person_slug'];
						$person_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('referee', $routeparameter);
					}

					$showname = HTMLHelper::link($person_link, $usedname);
					?>
                    <tr class="<?php echo $params->get('heading_style'); ?>">
                        <td class="birthday"><?php echo $showname; ?></td>
                    </tr>
                    <tr class="<?php echo $class; ?>">
                        <td class="birthday">
							<?php
							if ($params->get('show_picture') == 1)
							{
								if (file_exists(JPATH_BASE . '/' . $person['picture']) && $person['picture'] != '')
								{
									$thispic = $person['picture'];
								}
                                elseif (file_exists(JPATH_BASE . '/' . $person['default_picture']) && $person['default_picture'] != '')
								{
									$thispic = $person['default_picture'];
								}

								echo '<img src="' . Uri::base() . '/' . $thispic . '" alt="' . $text . '" title="' . $text . '"';

								if ($params->get('picture_height') != '')
								{
									echo ' width="auto" height="' . $params->get('picture_height') . '"';
								}

								echo ' /><br />';
							}


							switch ($person['days_to_birthday'])
							{
								case 0:
									$whenmessage = $params->get('todaymessage');
									break;
								case 1:
									$whenmessage = $params->get('tomorrowmessage');
									break;
								default:
									$whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));
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

							echo $birthdaytext;
							?></td>
                    </tr>
					<?php
					$k = 1 - $k;
					$counter++;
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
			<?php } ?>
        </table>
		<?PHP
		break;
}
