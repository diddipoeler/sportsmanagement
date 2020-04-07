<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagehandler
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
/**
*
 * welche joomla version
*/
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<div class="imghead">

	<?php echo Text::_('JSEARCH_FILTER_LABEL') . ' '; ?>
	<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onChange="document.getElementById('adminForm').submit();" />
	<button onclick="this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
	<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>

</div>

<div class="imglist">

	<?php
	for ($i = 0, $n = count($this->images); $i < $n; $i++)
	:
		$this->setImage($i);
		echo $this->loadTemplate('image');
	endfor;
	?>
</div>

<div class="clr"></div>

<div class="pnav"><?php echo $this->pageNav->getListFooter(); ?></div>

	<input type="hidden" name="option" value="com_sportsmanagement" />
	<input type="hidden" name="view" value="imagehandler" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="imagehandler.select" />
	<input type="hidden" name="folder" value="<?php echo $this->folder; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
