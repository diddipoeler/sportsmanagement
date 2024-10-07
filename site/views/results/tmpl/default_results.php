<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage results
 * @file       default_results.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;


if (!isset($this->project))
{
	Log::add(Text::_('Error: ProjectID was not submitted in URL or selected project was not found in database!'));
}
else
{
	?>
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultresults" >
        <br/>
<?php
/** pdf download */
if ( $this->config['show_button_download_pdf'] )
{
?>
<button onclick="javascript:downpdf('results')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/pdf.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'), array(' width' => 40));?>  PDF</button>
<?php
}

/** excel download */
if ( $this->config['show_button_download_excel'] )
{
?>
<button onclick="javascript:downexcel('results')"><?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/excel.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_EXCEL'), array(' width' => 40));?> EXCEL</button>
<?php
}

	?>    
        <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString()); ?>"
          method="post">
        <input type="hidden" name="limitstart" value=""/>
        <input type="hidden" name="view" value="<?php echo Factory::getApplication()->input->getVar('view'); ?>"/>
        <input type="hidden" name="option" value="<?php echo Factory::getApplication()->input->getCmd('option'); ?>"/>
        <input type="hidden" name="cfg_which_database"
               value="<?php echo Factory::getApplication()->input->getVar('cfg_which_database'); ?>"/>
        <input type="hidden" name="p" value="<?php echo Factory::getApplication()->input->getVar('p'); ?>"/>
        <input type="hidden" name="r" value="<?php echo Factory::getApplication()->input->getVar('r'); ?>"/>
        <input type="hidden" name="division"
               value="<?php echo Factory::getApplication()->input->getVar('division'); ?>"/>

               <?php
               if ( $this->view == 'results' )
               {
               ?>
        <div class="row">       
        <div class="display-limit col-lg-2 col-md-2 col-sm-2 col-xs-2">
			<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
        </div>

        <div class="  col-lg-5 col-md-5 col-sm-5 col-xs-5">
            <p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
            </p>
           
			
        </div>
          <div class="  col-lg-5 col-md-5 col-sm-5 col-xs-5">
           
            <p class="counter">
				<?php echo $this->pagination->getResultsCounter(); ?>
            </p>
			
        </div>
          
        </div>
        <?php
        }
        if ( $this->view == 'results' )
               {
        ?>
         <div class="row"> 
        <div class="  col-lg-12 col-md-12 col-sm-12 col-xs-12">
           <?php echo $this->pagination->getPagesLinks(); ?>
           </div>
           </div>
           
           
           
		<?php
        }
		if (count($this->matches) > 0)
		{
			switch ($this->config['result_style'])
			{
				case 4:
					{
						echo $this->loadTemplate('results_style_dfcday');
					}
					break;
				case 3:
					{
						echo $this->loadTemplate('results_style3');
					}
					break;
				case 0:
				case 1:
				case 2:
				default:
					{
						echo $this->loadTemplate('results_style0');
					}
					break;
			}
		}
		?>
        </form>
    </div>
    <!-- Main END -->
	<?php
	if ($this->config['show_dnp_teams'])
	{
		echo $this->loadTemplate('freeteams');
	}
}
