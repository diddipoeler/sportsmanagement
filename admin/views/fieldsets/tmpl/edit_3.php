<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      edit_3.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fieldsets
 */

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
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id.'&tmpl='.$this->tmpl); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
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
	
	
if ( $this->tmpl && $this->view == 'club' )
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
	
echo $this->loadTemplate('editdata');  
?>  
