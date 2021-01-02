<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage installhelper
 * @file       install_step_1.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/extended-1.1.css', 'text/css');
$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/style.css', 'text/css');
?>
<!--Tip Box-->
                <div class="color-box space">
                    <div class="shadow">
                        <div class="info-tab tip-icon" title="Useful Tips"><i></i></div>
                        <div class="tip-box">
                            <p><strong>Tip:</strong></p>
                        </div>
                    </div>
               </div>
<!--End:Tip Box-->
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
		<?PHP

		?>

        <table>
            <tr>
                <td class="nowrap" align="right"><?php echo $this->lists['sportstypes'] . '&nbsp;&nbsp;'; ?></td>
            </tr>
        </table>

       
       

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value=""/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>

		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
<div><?PHP echo $this->loadTemplate('footer');?></div>