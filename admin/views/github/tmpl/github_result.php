<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage github
 * @file       github_result.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');


// Welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('jquery.framework');
}



HTMLHelper::_('behavior.keepalive');




Factory::getDocument()->addScriptDeclaration(
	'
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

			Joomla.submitform(task, document.getElementById("addissue-formresult"));
		}
	};
'
);

?>

<div class="container-popup">

<div class="pull-right">
	<button class="btn" type="button" onclick="Joomla.submitbutton('github.cancel', this.form);"><?php echo Text::_('JCANCEL') ?></button>
</div>
<div class="clearfix"></div>

<form  action="<?php echo Route::_('index.php?option=com_sportsmanagement');?>" id='addissue-formresult' method='post' style='display:inline' name='adminform' >
<input type="hidden" name="component" value="<?PHP echo $this->option; ?>" />
<input type='hidden' name='task' value='' />
<input type="hidden" name="close" id="close" value="0" />
<input type="hidden" name="gh.token" value="<?PHP echo $this->gh_token; ?>" />
<input type="hidden" name="api.username" value="<?PHP echo $this->api_username; ?>" />
<input type="hidden" name="api.password" value="<?PHP echo $this->api_password; ?>" />
</form>

</div>
