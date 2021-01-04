<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage installhelper
 * @file       install_step_1.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

echo 'errors <pre>'.print_r($this->warnings,true).'</pre>';

//$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/extended-1.1.css', 'text/css');
//$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/style.css', 'text/css');
?>
<?php
if ( $this->jinput->get('error') )
{
$errors = implode("<br>",$this->jinput->get('errors'));  
?>
<!--Warning box rot -->
<div class="color-box">
					<div class="shadow">
						<div class="info-tab warning-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'); ?>"><i></i></div>
						<div class="warning-box">
							<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'); ?></strong>
                            <?php echo $errors; ?>
                            
                            </p>
						</div>
					</div>
</div>
<!--End:Warning box-->



<?php  
}

?>
<!--Tip Box grün -->
<!--                <div class="color-box space">
                    <div class="shadow">
                        <div class="info-tab tip-icon" title="Useful Tips"><i></i></div>
                        <div class="tip-box">
<p><strong>Tip:</strong></p>
                        </div>
                    </div>
               </div> -->
<!--End:Tip Box-->

<!--Note box blau -->
<div class="color-box">
<div class="shadow">
<div class="info-tab note-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE') ?>"><i></i></div>
<div class="note-box">
<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE') ?></strong>
<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_0') ?>
</p>
</div>
</div>
</div>
<!--End:Note box-->

<!--Warning box rot -->
<!-- <div class="color-box">
					<div class="shadow">
						<div class="info-tab warning-icon" title="Important Warnings"><i></i></div>
						<div class="warning-box">
							<p><strong>Warning:</strong></p>
						</div>
					</div>
				</div>  -->
<!--End:Warning box-->
                
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
		<?PHP

		?>

        <table class="table">
            <tr>
                <td class="nowrap" align="right"><?php echo $this->lists['sportstypes'] . '&nbsp;&nbsp;'; ?></td>
                <td><button type="button" onclick="Joomla.submitform('installhelper.savesportstype', this.form);">
						<?php echo Text::_('JAPPLY'); ?></button></td>
            </tr>
        </table>

       
       

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value=""/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>

		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
<div><?PHP echo $this->loadTemplate('footer');?></div>
