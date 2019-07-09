<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      add_issue.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage github
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
        {
HTMLHelper::_('jquery.framework');
}


HTMLHelper::_('behavior.keepalive');

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "github.cancel" )
		{
			' . '

			if (window.opener && task == "github.cancel" )
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout("window.document.closeEditWindow.close()", 1000);
			}

			Joomla.submitform(task, document.getElementById("addissue-form"));
		}
	};
');

?>

<div class="container-popup">

<div class="pull-right">
<button class="btn" type="button" onclick="Joomla.submitbutton('github.cancel', this.form);"><?php echo Text::_('JCANCEL') ?></button>
</div>

<div class="clearfix"></div>

	<fieldset class='adminform'>
		<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE','<i>'.'</i>'); ?></legend>
		<form  action="<?php echo Route::_('index.php?option=com_sportsmanagement');?>" id='addissue-form' method='post' style='display:inline' name='adminform' >
        
        <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('github.addissue', this.form)">
				<?php echo Text::_('JSAVE');?></button>

		</div>
		

        
	</fieldset>
    

			
			<?php echo HTMLHelper::_('form.token')."\n"; ?>
			<table class='table'>
            <tbody>
            <tr>
			
				<td >
                <?PHP
                echo $this->lists['labels'];
                ?>
                </td>
				<td>
                <?PHP
                echo $this->lists['milestones'];
                ?>
                </td>
			</tr>
            <tr>
            <td colspan="2">
            <?PHP
            echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_TITLE');
            ?>
            <input type="text" name="title" id="title" value="<?php echo $this->issuetitle; ?>" class="form-control form-control-inline" size="100" maxlength="100" required="" >
            </td>
            </tr>
            <tr >
            <td colspan="2">
            <?PHP
            echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_MESSAGE');
            ?>
            <div role="application" aria-labelledby="jform_projectinfo_voice" id="jform_projectinfo_parent" class="mceEditor defaultSkin">
            <textarea id="message" name="message" cols="" rows="" style="width:80%;height:200px;" class="wfEditor mce_editable source" wrap="off"></textarea>
            </div>
            </td>
            </tr>
            </tbody>
            </table>
            <input type="hidden" name="component" value="<?PHP echo $this->option; ?>" />
            <input type='hidden' name='task' value='' />
            <input type="hidden" name="close" id="close" value="0" />
            <input type="hidden" name="gh.token" value="<?PHP echo $this->gh_token; ?>" />
            <input type="hidden" name="api.username" value="<?PHP echo $this->api_username; ?>" />
            <input type="hidden" name="api.password" value="<?PHP echo $this->api_password; ?>" />
            
		</form>
	</fieldset>
</div>

<?PHP

?>