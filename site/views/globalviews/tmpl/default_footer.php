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

<style>
/*  #myModal1 .modal-dialog {
    width: 80%;
  }
*/  
.modal-dialog {
    width: 80%;
  }  
.modal-dialog,
.modal-content {
    /* 95% of window height */
    height: 95%;
}  
</style>

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

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>" style="text-align:center; clear:both">
      
      <br />      
      
              <a title= "<?php echo JText::_('COM_SPORTSMANAGEMENT_SITE_LINK')?>" target= "_blank" href="http://www.fussballineuropa.de">
                <img src= "<?php echo JURI::base( true ) ?>/administrator/components/com_sportsmanagement/assets/icons/logo_transparent.png" width="180" height="auto"</a>            
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
<!-- Button HTML (to Trigger Modal) -->
    <a href="<?php echo $cfg_bugtracker_server; ?>" class="" data-toggle="modal" data-target="#myModalBugTracker">Bug-Tracker</a>
<!-- Modal -->
<div class="modal fade" id="myModalBugTracker" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-remote="<?php echo $cfg_bugtracker_server; ?>">
  <div class="modal-dialog" >
    <div class="modal-content">
     <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">myModalBugTracker</h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
    
          
  </div>
</div>        
<!--      Bug-Tracker <a href="javascript:openLink('<?php echo $cfg_bugtracker_server; ?>')">Bug-Tracker</a> -->
      <br />
<!-- Button HTML (to Trigger Modal) -->
    <a href="<?php echo $cfg_help_server; ?>" class="" data-toggle="modal" data-target="#myModalOnlineHelp">Online-Help</a>      
<!-- Modal -->
<div class="modal fade" id="myModalOnlineHelp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-remote="<?php echo $cfg_help_server; ?>">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">myModalOnlineHelp</h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
    
     
    
  </div>
</div>  



<a href="http://www.wikipedia.de" data-target="#myModal" role="button" class="btn" data-toggle="modal">Launch demo modal</a>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
  
   <div class="modal-dialog" role="document">
    <div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
    <h3 id="myModalLabel">Modal header</h3>
  </div>
  
  <div class="modal-body">

  </div>
  
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary">Save changes</button>
  </div>
  
  </div>
  </div>
  
</div>

    
<!--      Online-Help <a href="javascript:openLink('<?php echo $cfg_help_server; ?>')">Online-Help</a> -->
      <br />   
     
              
    </div>   


            
<?php
//}
?>