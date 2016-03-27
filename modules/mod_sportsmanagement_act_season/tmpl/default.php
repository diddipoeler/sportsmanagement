<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$start = 1;
foreach ( $list as $row )
{

if ( $start == 1 )
{
?>    
<div class="row-">



<?PHP    
}    
$createroute = array(	"option" => "com_sportsmanagement",
							"view" => "ranking",
                            "cfg_which_database" => 0,
                            "s" => 0,
							"p" => $row->id,
                            "type" => 0,
              "r" => $row->roundcode,
              "from" => 0,
              "to" => 0,
              "division" => 0, );

$query = sportsmanagementHelperRoute::buildQuery( $createroute );
$link = JRoute::_( 'index.php?' . $query, false );
/*
<a href="#" class="btn primary">
  <img src="some_icon.png" class="pull-left"/>
  Text
</a>
*/
?>
<div class="col-sm-2">
<!-- <button type="button" class="btn btn-info btn-block"> -->


<a href="<?PHP echo $link;  ?>" class="<?PHP echo $params->get('button_class'); ?>  btn-block" role="button">
<span>
<?PHP
echo JSMCountries::getCountryFlag( $row->country );
?>
</span>
<?PHP
//echo JHTML::link( $link, JText::_( $row->name.' - ( '.$row->liganame.' )'  ) );
//echo JHTML::link( $link, JText::_( $row->name  ) );
echo JText::_( $row->name  );
?>
</a>
<!-- </button> -->
</div>
<?PHP
$start++;
if ( $start == 7 )
{
$start = 1;  
?>
</div>
<?PHP
}
    
}
?>