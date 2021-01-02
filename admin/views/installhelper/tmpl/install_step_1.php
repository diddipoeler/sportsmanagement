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

$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/extended-1.1.css', 'text/css');
$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/style.css', 'text/css');
?>

<style>  
.color-box{margin:15px 0;padding-left:20px}  
.space{margin-bottom:25px!important}


.shadow{background:#F7F8F9;padding:3px;margin:10px 0}
        
.tip-box{color:#2e5014;background:#d5efc2}        
.info-tab{width:40px;height:40px;display:inline-block;position:relative;top:8px}
.info-tab::before,.info-tab::after{display:inline-block;color:#fff;line-height:normal;font-family:"icomoon";position:absolute}
.info-tab i::before,.info-tab i::after{content:"";display:inline-block;position:absolute;left:0;bottom:-15px;transform:rotateX(60deg)}
.info-tab i::before{width:20px;height:20px;box-shadow:inset 12px 0 13px rgba(0,0,0,0.5)}
.info-tab i::after{width:0;height:0;border:12px solid transparent;border-bottom-color:#fff;border-left-color:#fff;bottom:-18px}
                   
.tip-icon{background:#92CD59}
//.note-icon{background:#47ADE0}
//.tip-icon{background:#47ADE0}                   
.warning-icon{background:#AD3C3C}           
          
.note-box,.warning-box,.tip-box{padding:8px 8px 3px 26px}          
.info-tab{float:left;margin-left:-23px}
              
  </style>



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