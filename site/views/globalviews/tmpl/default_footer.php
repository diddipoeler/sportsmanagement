<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault_footer.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined('_JEXEC') or die('Restricted access');

$option = JFactory::getApplication()->input->getCmd('option');
$view = JFactory::getApplication()->input->getVar( "view") ;
$view = ucfirst(strtolower($view));
$cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
$modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
$modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
$cfg_bugtracker_server = JComponentHelper::getParams($option)->get('cfg_bugtracker_server','') ;	
$logo_width = JComponentHelper::getParams($option)->get('logo_picture_width',100) ;
?>

<style>
.modaljsm {
    width: 80%;
    height: 60%;
  }  
  

</style>


<style>
.modal-dialog {
    width: 80%;
  }  
.modal-dialog,
.modal-content {
    /* 95% of window height */
    height: 95%;
}  
</style>

<script type="text/javascript" >

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

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align:center; clear:both">

<br />      
      
<a title= "<?php echo JText::_('COM_SPORTSMANAGEMENT_SITE_LINK')?>" target="_blank" href="http://www.fussballineuropa.de">
<img src= "<?php echo  JURI::root( true );?>/components/com_sportsmanagement/assets/images/logo_transparent.png" width="<?PHP echo $logo_width; ?>" height="auto"></a>            
	<br />
	<?php echo JText::_( "COM_SPORTSMANAGEMENT_DESC" ); ?>
	<br />      
<img src= "<?php echo  JURI::root( true );?>/components/com_sportsmanagement/assets/images/fussballineuropa.png" width="<?PHP echo $logo_width; ?>" height="auto"></a>            		
	<?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?> : &copy;
	<a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
<br />  
<img src= "<?php echo  JURI::root( true );?>/components/com_sportsmanagement/assets/images/facebook.png" width="<?PHP echo $logo_width; ?>" height="auto"></a>            		
<a href="https://www.facebook.com/joomlasportsmanagement/" target="_blank">JSM auf Facebook</a>	

	<br />      
	<?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?> :       
	<?php 
		//echo JText::sprintf( '%1$s', sportsmanagementHelper::getVersion() );
	echo JHtml::link('index.php?option='.$option.'&amp;view=about',sprintf('Version %1$s (diddipoeler)',sportsmanagementHelper::getVersion()));
	?>
	<br />    
      
<?PHP
// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{

}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
?>
<!-- Button HTML (to Trigger Modal) -->
<a href="<?php echo $cfg_bugtracker_server; ?>" rel="modaljsm:open">Bug-Tracker</a>
<br />
<a href="<?php echo $cfg_help_server; ?>" rel="modaljsm:open">Online-Help</a>
<br />
<?PHP
} 

?>      


</div>


            
<?php
//}
?>
