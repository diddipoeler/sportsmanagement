<?php
/**
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       deafult_projectinfo.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>
<div class="<?php echo $this->divclassrow; ?>" id="projectinfo">
<!--Note box blau -->
<div class="color-box">
					<div class="shadow">
						<div class="info-tab note-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_PROJECTINFO'); ?>"><i></i></div>
						<div class="note-box">
							<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_PROJECTINFO'); ?></strong>
                            </p>
						</div>
					</div>
</div>
<!--Note box blau -->

    <table class="<?PHP echo $this->config['table_class']; ?>">
        <tr>
            <td align="left">
			<span class="<?PHP echo $this->config['label_class_teams']; ?>">
				<?php
				if ($this->projectinfo)
				{
?>				    
<!--Tip Box grün -->
<div class="color-box">
					<div class="shadow">
						<div class="info-tab tip-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'); ?>"><i></i></div>
						<div class="tip-box">
							<p><strong><?php echo $this->projectinfo; ?></strong>
                            </p>
						</div>
					</div>
</div>
<!--Tip Box grün -->
<?php                    
				}
				else
				{
					?>
<!--Tip Box grün -->
<div class="color-box">
					<div class="shadow">
						<div class="info-tab tip-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'); ?>"><i></i></div>
						<div class="tip-box">
							<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'); ?></strong>
                            </p>
						</div>
					</div>
</div>
<!--Tip Box grün -->                     
					<?PHP
				}
				?>
			</span>
            </td>
        </tr>
    </table>
</div>
