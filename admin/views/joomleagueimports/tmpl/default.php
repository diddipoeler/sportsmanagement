<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage joomleagueimports
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('jquery.framework');
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if ( $this->jl_table_import_step != 'ENDE' && is_numeric($this->jl_table_import_step) )
{
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
   console.log('document is ready. I can sleep now');
   document.getElementById('delayMsg').innerHTML = '';
            delayRedirect();
            const stepsuccess = Joomla.getOptions('success');
            console.log('stepsuccess ' + stepsuccess);
});
//        jQuery(document).ready(function () {
//            document.getElementById('delayMsg').innerHTML = '';
//            delayRedirect();
//            const stepsuccess = Joomla.getOptions('success');
//            console.log('stepsuccess ' + stepsuccess);
//        });

        function delayRedirect() {
            document.getElementById('delayMsg').innerHTML = '<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP'); ?>';
            var count = 3;
            setInterval(function () {
                count--;
                document.getElementById('countDown').innerHTML = count;
                if (count == 0) {
                    document.getElementById('delayMsg').innerHTML = '<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP_START'); ?>';
                    window.location = '<?php echo $this->request_url . '&task=joomleagueimports.importjoomleaguenew'; ?>';
                }
            }, 1000);
        }
</script>
<?PHP
}

if ($this->jl_table_import_step === 'ENDE')
{
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
   console.log('document is ready. I can sleep now');
   document.getElementById('delayMsg').innerHTML = '';
            delayRedirect();
});
//        jQuery(document).ready(function () {
//            document.getElementById('delayMsg').innerHTML = '';
//            delayRedirect();
//            // Handler for .ready() called.
////    window.setTimeout(function () {
////        location.href = "<?php echo $this->request_url . '&task=joomleagueimports.importjoomleaguenew'; ?>";
////    }, 2000);
//        });

        function delayRedirect() {
            document.getElementById('delayMsg').innerHTML = '<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP'); ?>';
            var count = 5;
            setInterval(function () {
                count--;
                document.getElementById('countDown').innerHTML = count;
                if (count == 0) {
                    document.getElementById('delayMsg').innerHTML = '<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_STEP_START'); ?>';
                    window.location = '<?php echo $this->request_url . '&task=joomleagueimports.importjoomleagueagegroup'; ?>';
                }
            }, 1000);
        }
    </script>
<?PHP
}

?>
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
		<?PHP

		?>

        <table>
            <tr>
                <td class="nowrap" align="right"><?php echo $this->lists['sportstypes'] . '&nbsp;&nbsp;'; ?></td>
            </tr>
        </table>

        <table class="<?php echo $this->table_data_class; ?>">
            <tr>
                <td class="nowrap" align="center">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/jl.png"
                         width="180" height="auto">
                </td>
                <td class="nowrap" align="center">
                    <div id="delayMsg"></div>
                    <div id="countDown"></div>
                </td>
                <td class="nowrap" align="center">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/logo_transparent.png"
                         width="180" height="auto">
                </td>
            </tr>
        </table>

        <div id='editcell'>
			<?PHP
			if ($this->success)
			{
				foreach ($this->success as $key => $value)
				{
					?>
                    <fieldset>
                        <legend><?php echo Text::_($key); ?></legend>
                        <table class='adminlist'>
                            <tr>
                                <td><?php echo $value; ?></td>
                            </tr>
                        </table>
                    </fieldset>
					<?php
				}
			}
			?>
        </div>


        <fieldset>
            <legend><?php echo Text::_('Post data from importform was:'); ?></legend>
            <table class='adminlist'>
                <tr>
                    <td>
                    </td>
                </tr>
            </table>
        </fieldset>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value=""/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
        <input type="hidden" name="jl_table_import_step" value="<?php echo $this->jl_table_import_step; ?>"/>

		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
<div><?PHP echo $this->loadTemplate('footer');?></div>
