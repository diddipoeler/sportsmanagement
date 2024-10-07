<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allprojectrounds
 * @file       default_results_all.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<!-- Main START -->
<div class="row-fluid" id="">
<!-- content -->
<?php

/** pdf download */
if ( $this->config['show_button_download_pdf'] )
{
?>
<button onclick="javascript:downpdf('allprojectrounds')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/pdf.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'), array(' width' => 40));?>  PDF</button>
<?php
}

/** excel download */
if ( $this->config['show_button_download_excel'] )
{
?>
<button onclick="javascript:downexcel('allprojectrounds')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/excel.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_EXCEL'), array(' width' => 40));?> EXCEL</button>
<?php
}
  
?>
<table class="<?php echo $this->tableclass; ?>" id="allprojectrounds">
<tr>
<td class="">
<?php
echo $this->content;
?>
</td>
</tr>
</table>
<?php
?>
<!-- all results END -->
</div>
