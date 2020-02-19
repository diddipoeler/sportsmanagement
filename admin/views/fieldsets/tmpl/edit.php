<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      edit.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fieldsets
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
/**
 * Include the component HTML helpers.
 */
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');


$params = $this->form->getFieldsets('params');

/**
 * Get the form fieldsets.
 */
$fieldsets = $this->form->getFieldsets();

?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id.'&tmpl='.$this->tmpl); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
 
<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('details') as $field) :?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                
                if ( $field->name == 'jform[country]' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                if ( $field->name == 'jform[website]' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                
                $suchmuster = array ("jform[","]");
                $ersetzen = array ('', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                
                switch ($var_onlinehelp)
                {
                    case 'id':
                    break;
                    default:
                ?>
                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
									href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.Factory::getApplication()->input->getVar( "view") . '-' . $this->form->getName() . '-' . $var_onlinehelp; ?>"
									 class="modal">
									<?php
									echo HTMLHelper::_(	'image','media/com_sportsmanagement/jl_images/help.png',
													Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
													Text::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
									?>
								</a>
                
                <?PHP
                break;
                }
                
                ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>

<?PHP
echo $this->loadTemplate('editdata');
?>
