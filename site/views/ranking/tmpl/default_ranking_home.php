<?php
/**
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       deafult_ranking_home.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** pdf download */
if ( $this->config['show_button_download_pdf'] )
{
?>
<button onclick="javascript:downpdf('rankingall')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/pdf.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'), array(' width' => 40));?>  PDF</button>
<?php
}

/** excel download */
if ( $this->config['show_button_download_excel'] )
{
?>
<button onclick="javascript:downexcel('rankingall')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/excel.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_EXCEL'), array(' width' => 40));?> EXCEL</button>
<?php
}
?>

<!-- Main START -->
<a name="jl_top" id="jl_top"></a>

<!-- content -->
<?php
foreach ($this->homeRank as $division => $cu_rk)
{
	if ($division)
	{
		?>
        <div class="<?php echo $this->divclassrow; ?> table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
                <tr>
                    <td class="contentheading">
						<?php
						// Get the division name from the first team of the division
						foreach ($cu_rk as $ptid => $team)
						{
							echo $this->divisions[$division]->name;
							break;
						}
						?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="<?php echo $this->divclassrow; ?> table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
				<?php
				foreach ($cu_rk as $ptid => $team)
				{
					echo $this->loadTemplate('rankingheading');
					break;
				}

				$this->division = $division;
				$this->current  = &$cu_rk;
				$this->teamrow  = 'hr';
				echo $this->loadTemplate('rankingrows');
				?>
            </table>
        </div>
		<?php
	}
	else
	{
		?>
        <div class="<?php echo $this->divclassrow; ?> table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>" id="rankinghome">
				<?php
				echo $this->loadTemplate('rankingheading');
				$this->division = $division;
				$this->current  = &$cu_rk;
				$this->teamrow  = 'hr';
				echo $this->loadTemplate('rankingrows');
				?>
            </table>
        </div>
        <br/>
		<?php
	}
}
?>
<!-- ranking END -->



