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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
//echo 'this->matchimages<br /><pre>~' . print_r($this->matchimages,true) . '~</pre><br />';
$my_text = 'matchimages <pre>'.print_r($this->matchimages,true).'</pre>';
//$my_text .= 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';   
//$my_text .= 'cards <pre>'.print_r($cards,true).'</pre>';       
sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,'sportsmanagementViewMatchReportdefault_pictures',__LINE__,$my_text);
        
}

$actualItems = count( $this->matchimages );
$setItems    = count( $this->matchimages ) ;
$rssitems_colums = $this->config['show_pictures_columns'] ;
$pictures_width = $this->config['show_pictures_width'] ;

if ($setItems > $actualItems) {
			$totalItems = $actualItems;
		} else {
			$totalItems = $setItems;
		}
    
?>

<table cellpadding="0" cellspacing="0" class="moduletable<?php //echo $params->get('moduleclass_sfx'); ?>">
<?php
$j = 0;
foreach ( $this->matchimages as $images )
{
// echo JHtml::image($images->sitepath.DS.$images->name, $images->name , array('title' => $images->name ,'width' => "200" ));
// echo '<br>';

if (($j % $rssitems_colums) == 0 ) : 
// 						if ( $this->overallconfig['rssrow_alternate'] ) {
							$row = 'row'.(floor($j / $rssitems_colums) % $rssitems_colums) ;
// 						} else {
// 							$row = 'row0';
// 						}

?>
					<tr class="<?php echo $row; ?>">
					<?php endif; ?>
					<td class="item" style="width:<?php echo floor(99/$rssitems_colums)."%";?>">
          <a href="<?php echo $images->sitepath.DS.$images->name;?>" alt="<?php echo $images->name;?>" title="<?php echo $images->name;?>" class="highslide" onclick="return hs.expand(this)">
					<?php
echo JHtml::image($images->sitepath.DS.$images->name, $images->name , array('title' => $images->name ,'width' => $pictures_width ));					
					?>
          </a>
					</td>
					<?php if (($j % $rssitems_colums) == ($rssitems_colums-1) ) : ?>
					</tr>
					<?php endif; ?>
					<?php						
						
$j++;
}
?>
</table>		