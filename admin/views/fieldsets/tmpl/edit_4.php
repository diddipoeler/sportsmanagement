<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       edit_4.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Jimport( 'joomla.html.html.tabs' );
jimport('joomla.html.pane');

try
{
$params = $this->form->getFieldsets('params');
}
catch (Exception $e)
{
Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
return false;
}

try
{
$fieldsets = $this->form->getFieldsets();
}
catch (Exception $e)
{
Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
return false;
}
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=' . $this->view . '&layout=edit&id=' . (int) $this->item->id . '&tmpl=' . $this->tmpl); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<?PHP
if ($this->tmpl)
{
	?>
			<fieldset>
				<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('club.apply', this.form);">
		<?php echo Text::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('club.save', this.form);">
		<?php echo Text::_('JSAVE');?></button>
					<button type="button" onclick="Joomla.submitform('club.cancelmodal', this.form);">
		<?php echo Text::_('JCANCEL');?></button>

			  
							  </div>

						  </fieldset>
<?PHP
}
?>

<?PHP

if (!$this->item->id && $this->view == 'club')
{
									?>
				<fieldset class="adminform">
			<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CREATE_TEAM'); ?></legend>
				<input type="checkbox" name="createTeam" checked="checked"/>
				</fieldset>
				<?PHP
}

			  echo $this->loadTemplate('editdata');


