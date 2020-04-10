<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage treetomatchs
 * @file       editlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.tooltip');

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

<?php

?>
<script>
    Joomla.submitbutton = function (task) {
        jQuery('select#node_matcheslist > option').prop('selected', 'selected');
//        if (task == "simplelistitem.cancel"){
//           Joomla.submitform(task, thisForm );
//        }
//        else if (document.formvalidator.isValid(thisForm))
//        {
//            //add any additional validation here
//            if(jQuery('aa').val() === 'aaa'){
//                Joomla.submitform(task, thisForm );
//            }
//        }
        Joomla.submitform(task);
        return true;
    };

</script>

<style type="text/css">
    table.paramlist td.paramlist_key {
        width: 92px;
        text-align: left;
        height: 30px;
    }
</style>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <div class="col50">

		<?php
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			echo $this->loadTemplate('joomla3');
		}
		else
		{
			echo $this->loadTemplate('joomla2');
		}

		echo $this->loadTemplate('data');
		?>

        <div class="clr"></div>
        <input type="hidden" name="matcheschanges_check" value="0" id="matcheschanges_check"/>
        <input type="hidden" name="option" value="com_sportsmanagement"/>
        <input type="hidden" name="cid[]" value="<?php echo $this->nodews->id; ?>"/>
        <input type="hidden" name="task" value="treetomatch.save_matcheslist"/>
    </div>
</form>
