<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
JHtml::_( 'behavior.modal' );

$option = JRequest::getCmd('option');
$view = JRequest::getVar( "view") ;
$view = ucfirst(strtolower($view));
$cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
$modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
$cfg_bugtracker_server = JComponentHelper::getParams($option)->get('cfg_bugtracker_server','') ;	
//if (JComponentHelper::getParams('com_sportsmanagement')->get('show_footer',0))
//{
?>

<script>

function openLink(url)
{
var width = get_windowPopUpWidth();
var heigth = get_windowPopUpHeight(); 

SqueezeBox.open(url, {
       handler: 'iframe', 
       size: { x: width, y: heigth }
   });
       
} 

</script>	

<div style="text-align:center; clear:both">      
      
      <br />      
      
              <a title= "<?php echo JText::_('COM_SPORTSMANAGEMENT_SITE_LINK')?>" target= "_blank" href="http://www.fussballineuropa.de">
                <img src= "<?php echo JUri::base( true ) ?>/administrator/components/com_sportsmanagement/assets/icons/logo_transparent.png" width="180" height="auto"</a>            
      <br />
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_DESC" ); ?>
      <br />      
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?> : &copy; 
      <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
      <br />      
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?> :       
      <?php 
      //echo JText::sprintf( '%1$s', sportsmanagementHelper::getVersion() ); 
      echo JHtml::link('index.php?option='.$option.'&amp;view=about',sprintf('Version %1$s (diddipoeler)',sportsmanagementHelper::getVersion()));
      ?>
      <br />      
      Bug-Tracker <a href="javascript:openLink('<?php echo $cfg_bugtracker_server; ?>')">Bug-Tracker</a>
      <br />
      
      Online-Help <a href="javascript:openLink('<?php echo $cfg_help_server; ?>')">Online-Help</a>
      <br />   
     
              
    </div>   


            
<?php
//}
?>