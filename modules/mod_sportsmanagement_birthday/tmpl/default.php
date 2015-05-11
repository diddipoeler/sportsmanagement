<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access'); 


switch ($mode)
{
	// bootstrap mode template
	case 'B':

?>    
<div class="row">
        <div class="row">
           
            <div class="col-md-12">
                <!-- Controls -->
                <div class="controls pull-right hidden-xs">
                    <a class="left fa fa-chevron-left btn btn-primary" href="#carousel-<?php echo $module->module; ?>-<?php echo $module->id; ?>"
                        data-slide="prev"></a><a class="right fa fa-chevron-right btn btn-primary" href="#carousel-<?php echo $module->module; ?>-<?php echo $module->id; ?>"
                            data-slide="next"></a>
                </div>
            </div>
        </div>
        
        <div id="carousel-<?php echo $module->module; ?>-<?php echo $module->id; ?>" class="carousel slide hidden-xs" data-ride="carousel">
            <!-- Wrapper for slides -->
            <div class="carousel-inner">
            
<?PHP
$a = 0;
foreach ($persons AS $person) 
{
$text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], 
													$person['nickname'], 
													$person['lastname'], 
													$params->get("name_format")), ENT_QUOTES, 'UTF-8');
                                                        
$active = ($a==0) ? 'active' : '';    
if ($params->get('show_picture')==1) 
{
if (file_exists(JPATH_BASE.'/'.$person['picture'])&&$person['picture']!='') 
{
$thispic = $person['picture'];
}
elseif (file_exists(JPATH_BASE.'/'.$person['default_picture'])&&$person['default_picture']!='') 
{
$thispic = $person['default_picture'];
}
}

switch ($person['days_to_birthday']) 
{
case 0: $whenmessage = $params->get('todaymessage');break;
case 1: $whenmessage = $params->get('tomorrowmessage');break;
default: $whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));break;
}
        
$birthdaytext = htmlentities(trim(JText::_($params->get('birthdaytext'))), ENT_COMPAT , 'UTF-8');
$dayformat = htmlentities(trim($params->get('dayformat')));
$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
$birthdaytext = str_replace('%WHEN%', $whenmessage, $birthdaytext);
$birthdaytext = str_replace('%AGE%', $person['age'], $birthdaytext);
$birthdaytext = str_replace('%DATE%', strftime($dayformat, strtotime($person['year'].'-'.$person['daymonth'])), $birthdaytext);
$birthdaytext = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($person['date_of_birth'])), $birthdaytext);
$birthdaytext = str_replace('%BR%', '<br />', $birthdaytext);
$birthdaytext = str_replace('%BOLD%', '<b>', $birthdaytext);
$birthdaytext = str_replace('%BOLDEND%', '</b>', $birthdaytext);

?>              
                <div class="item <?php echo $active; ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-item">
                                <div class="photo">
                                    <img src="<?php echo $thispic; ?>" class="img-responsive" alt="a" />
                                </div>
                            </div>
                        </div>        
        </div>
        
        <div class="info">
                                    <div class="row">
                                    <div class="price col-md-6">
                                            <h5><?php echo $text; ?></h5>
                                            
                                        </div>
                                    <div class="price col-md-6">
                                            
                                            <h5 class="price-text-color"><?php echo $birthdaytext; ?></h5>
                                        </div>    
                                    </div>
                                    </div>
        </div>  
<?PHP 
$a++;   
}
?>            
        </div>
</div>
</div>         
<?PHP    
    break;
    // bootstrap mode template
	case 'B2':

?>
<div id="myBirthday<?php echo $module->id; ?>" class="carousel slide" data-interval="3000" data-ride="carousel">
<!-- Indicators -->
<ol class="carousel-indicators">
<?PHP
for ($a=0; $a < count($persons);$a++)
{
$active = ($a==0) ? 'class="active"' : '';    
?>    
<li data-target="#myBirthday<?php echo $module->id; ?>" data-slide-to="<?php echo $a; ?>" <?php echo $active; ?> ></li></li>
<?PHP    
}
?>
</ol>

<!-- Wrapper for slides -->
<div class="carousel-inner" role="listbox">
<?PHP
$a = 0;
foreach ($persons AS $person) 
{
$active = ($a==0) ? 'active' : '';  
$thispic = "";
$text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], 
													$person['nickname'], 
													$person['lastname'], 
													$params->get("name_format")), ENT_QUOTES, 'UTF-8');
if ($params->get('show_picture')==1) 
{
if (file_exists(JPATH_BASE.'/'.$person['picture'])&&$person['picture']!='') 
{
$thispic = $person['picture'];
}
elseif (file_exists(JPATH_BASE.'/'.$person['default_picture'])&&$person['default_picture']!='') 
{
$thispic = $person['default_picture'];
}
}

switch ($person['days_to_birthday']) 
{
case 0: $whenmessage = $params->get('todaymessage');break;
case 1: $whenmessage = $params->get('tomorrowmessage');break;
default: $whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));break;
}
        
$birthdaytext = htmlentities(trim(JText::_($params->get('birthdaytext'))), ENT_COMPAT , 'UTF-8');
$dayformat = htmlentities(trim($params->get('dayformat')));
$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
$birthdaytext = str_replace('%WHEN%', $whenmessage, $birthdaytext);
$birthdaytext = str_replace('%AGE%', $person['age'], $birthdaytext);
$birthdaytext = str_replace('%DATE%', strftime($dayformat, strtotime($person['year'].'-'.$person['daymonth'])), $birthdaytext);
$birthdaytext = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($person['date_of_birth'])), $birthdaytext);
$birthdaytext = str_replace('%BR%', '<br />', $birthdaytext);
$birthdaytext = str_replace('%BOLD%', '<b>', $birthdaytext);
$birthdaytext = str_replace('%BOLDEND%', '</b>', $birthdaytext);
        
?>    
<div class="item <?php echo $active; ?>">
<img src="<?php echo $thispic; ?>" alt="<?php echo $text; ?>" width="<?php echo $params->get('picture_width'); ?>"  >
<div class="carousel-caption">
<h3><?php echo $text; ?></h3>
<p><?php echo $birthdaytext; ?></p>
</div>
</div>
<?PHP 
$a++;   
}
?>

</div>
<!-- Left and right controls -->
<a class="left carousel-control" href="#myBirthday<?php echo $module->id; ?>" role="button" data-slide="prev">
<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
<span class="sr-only">Previous</span>
</a>
<a class="right carousel-control" href="#myBirthday<?php echo $module->id; ?>" role="button" data-slide="next">
<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
<span class="sr-only">Next</span>
</a>
</div>
<?PHP    

    break;
    default:
?>

<table class="table">
<?php
if(count($persons) > 0) 
{
	foreach ($persons AS $person) 
    {
		if (($params->get('limit')> 0) && ($counter == intval($params->get('limit')))) break;
		$class = ($k == 0)? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');

		$thispic = "";
		$flag = $params->get('show_player_flag')? JSMCountries::getCountryFlag($person['country']) . "&nbsp;" : "";
		$text = htmlspecialchars(sportsmanagementHelper::formatName(null, $person['firstname'], 
													$person['nickname'], 
													$person['lastname'], 
													$params->get("name_format")), ENT_QUOTES, 'UTF-8');
		$usedname = $flag.$text;
		
		$person_link = "";
		$person_type = $person['type'];
        
		if($person_type==1) 
        {
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $person['project_slug'];
$routeparameter['tid'] = $person['team_slug'];
$routeparameter['pid'] = $person['person_slug'];
$person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);

		} 
        else if($person_type==2) 
        {
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $person['project_slug'];
$routeparameter['tid'] = $person['team_slug'];
$routeparameter['pid'] = $person['person_slug'];
$person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);

		} 
        else if($person_type==3) 
        {
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $person['project_slug'];
$routeparameter['pid'] = $person['person_slug'];
$person_link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);

		}
		$showname = JHTML::link( $person_link, $usedname );
		?>
	<tr class="<?php echo $params->get('heading_style');?>">
		<td class="birthday"><?php echo $showname;?></td>
	</tr>
	<tr class="<?php echo $class;?>">
		<td class="birthday">
		<?php
		if ($params->get('show_picture')==1) {
			if (file_exists(JPATH_BASE.'/'.$person['picture'])&&$person['picture']!='') {
				$thispic = $person['picture'];
			}
			elseif (file_exists(JPATH_BASE.'/'.$person['default_picture'])&&$person['default_picture']!='') {
				$thispic = $person['default_picture'];
			}
			echo '<img src="'.JURI::base().'/'.$thispic.'" alt="'.$text.'" title="'.$text.'"';
			if ($params->get('picture_width') != '') echo ' width="'.$params->get('picture_width').'"';
			echo ' /><br />';

		}
		switch ($person['days_to_birthday']) {
			case 0: $whenmessage = $params->get('todaymessage');break;
			case 1: $whenmessage = $params->get('tomorrowmessage');break;
			default: $whenmessage = str_replace('%DAYS_TO%', $person['days_to_birthday'], trim($params->get('futuremessage')));break;
		}
		$birthdaytext = htmlentities(trim(JText::_($params->get('birthdaytext'))), ENT_COMPAT , 'UTF-8');
		$dayformat = htmlentities(trim($params->get('dayformat')));
		$birthdayformat = htmlentities(trim($params->get('birthdayformat')));
		$birthdaytext = str_replace('%WHEN%', $whenmessage, $birthdaytext);
		$birthdaytext = str_replace('%AGE%', $person['age'], $birthdaytext);
		$birthdaytext = str_replace('%DATE%', strftime($dayformat, strtotime($person['year'].'-'.$person['daymonth'])), $birthdaytext);
		$birthdaytext = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($person['date_of_birth'])), $birthdaytext);
		$birthdaytext = str_replace('%BR%', '<br />', $birthdaytext);
		$birthdaytext = str_replace('%BOLD%', '<b>', $birthdaytext);
		$birthdaytext = str_replace('%BOLDEND%', '</b>', $birthdaytext);
			
		echo $birthdaytext;
		?></td>
	</tr>
	<?php
	$k = 1 - $k;
	$counter++;
	}
}
else {
?>
<tr>
	<td class="birthday"><?php echo''.str_replace('%DAYS%', $params->get('maxdays'), htmlentities(trim($params->get('not_found_text')))).''; ?></td>
</tr>
<?php } ?>
</table>
<?PHP

break;
}