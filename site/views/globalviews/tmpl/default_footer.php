<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       deafault_footer.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$option                = Factory::getApplication()->input->getCmd('option');
$view                  = Factory::getApplication()->input->getVar("view");
$view                  = ucfirst(strtolower($view));
$cfg_help_server       = ComponentHelper::getParams($option)->get('cfg_help_server', '');
$modal_popup_width     = ComponentHelper::getParams($option)->get('modal_popup_width', 0);
$modal_popup_height    = ComponentHelper::getParams($option)->get('modal_popup_height', 0);
$show_facebook_link    = ComponentHelper::getParams($option)->get('show_facebook_link', 0);
$cfg_bugtracker_server = ComponentHelper::getParams($option)->get('cfg_bugtracker_server', '');
$logo_width            = ComponentHelper::getParams($option)->get('logo_picture_width', 100);
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

<script type="text/javascript">

    function openLink(url) {
        var width = get_windowPopUpWidth();
        var heigth = get_windowPopUpHeight();

        SqueezeBox.open(url, {
            handler: 'iframe',
            size: {x: width, y: heigth}
        });

    }

</script>


  
  
<div class="container text-center d-flex align-items-center justify-content-center">
 <br/>
	<?php echo Text::_("COM_SPORTSMANAGEMENT_DESC"); ?>
    <br/>
    <img src="<?php echo Uri::root(true); ?>/components/com_sportsmanagement/assets/images/fussballineuropa.png"
         width="<?PHP echo $logo_width; ?>" height="auto"></a>
	<?php echo Text::_("COM_SPORTSMANAGEMENT_COPYRIGHT"); ?> : &copy;
    <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
    <br/>
	<?php
	if ($show_facebook_link == 3)
	{
		?>
        <img src="<?php echo Uri::root(true); ?>/components/com_sportsmanagement/assets/images/facebook.png"
             width="<?PHP echo $logo_width; ?>" height="auto"></a>
        <a href="https://www.facebook.com/joomlasportsmanagement/" target="_blank">JSM auf Facebook</a>
        <br/>
		<?php
	}
	?>
	<?php echo Text::_("COM_SPORTSMANAGEMENT_VERSION"); ?> :
	<?php
	// Echo HTMLHelper::link('index.php?option='.$option.'&amp;view=about',sprintf('Version %1$s (diddipoeler)',sportsmanagementHelper::getVersion()));
	echo sprintf('%1$s (diddipoeler)', sportsmanagementHelper::getVersion());
	?>
    <br/>

<div class="center">
<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_FOOTER_TIME', $this->jsmseitenaufbau); ?>
</div>
  
<?php
$link_onlinehelp = $cfg_help_server . 'SM-Frontend:' . $view ;                                                
$cmd = "Joomla.popupWindow('$link_onlinehelp', '" . Text::_('COM_SPORTSMANAGEMENT_HELP_LINK', true) . "',". $modal_popup_width." ,". $modal_popup_height.", 1)";
?>
<div class="center">
<button onclick="<?php echo $cmd; ?>">
<?php
echo HTMLHelper::_(
'image', 'media/com_sportsmanagement/jl_images/help.png',
Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'), 'title= "' .
Text::_('COM_SPORTSMANAGEMENT_HELP_LINK') . '"'
);
echo ' '.Text::_('COM_SPORTSMANAGEMENT_HELP_LINK');
?>                      
</button>
</div>  
  
  
</div>    
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
