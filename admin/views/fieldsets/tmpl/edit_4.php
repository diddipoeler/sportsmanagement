<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      edit_4.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fieldsets
 */

//$cfg_help_server = JComponentHelper::getParams('com_sportsmanagement')->get('cfg_help_server','') ;
//$modal_popup_width = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_width',0) ;
//$modal_popup_height = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_height',0) ;

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
//jimport( 'joomla.html.html.tabs' );
jimport('joomla.html.pane');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.modal');
try{
$params = $this->form->getFieldsets('params');
}
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}

//$options = array(
//    'onActive' => 'function(title, description){
//        description.setStyle("display", "block");
//        title.addClass("open").removeClass("closed");
//    }',
//    'onBackground' => 'function(title, description){
//        description.setStyle("display", "none");
//        title.addClass("closed").removeClass("open");
//    }',
//    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
//    'useCookie' => true, // this must not be a string. Don't use quotes.
//);

try{
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
}
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id .'&tmpl='.$this->tmpl); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<?PHP
if ( $this->tmpl )
{
		?>
			<fieldset>
				<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('club.apply', this.form);">
						<?php echo Text::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('club.save', this.form);">
						<?php echo Text::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo Factory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo Text::_('JCANCEL');?></button>
				
                
                </div>
				
			</fieldset>
<?PHP                
}
?> 

<?PHP

if ( !$this->item->id && $this->view == 'club' )
                {
                    
                ?>
                <fieldset class="adminform">
			<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CREATE_TEAM'); ?></legend>
                <input type="checkbox" name="createTeam" />
                </fieldset>
                <?PHP
                }               
                
echo $this->loadTemplate('editdata');                
?>
                
