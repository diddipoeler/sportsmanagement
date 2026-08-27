<?php
/**
 * SportsManagement results list template.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

if (!isset($this->project)) {
    Log::add(Text::_('Error: ProjectID was not submitted in URL or selected project was not found in database!'));
    return;
}

$app = Factory::getApplication();
$input = $app->getInput();
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultresults">
    <br/>
    <?php
    if ($this->view === 'results') {
        if ($this->config['show_button_download_pdf']) {
            ?>
            <button type="button" onclick="downpdf('results')">
                <?php
                echo HTMLHelper::_(
                    'image',
                    'media/com_sportsmanagement/jl_images/pdf.png',
                    Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'),
                    ['width' => 40]
                );
                ?> PDF
            </button>
            <?php
        }

        if ($this->config['show_button_download_excel']) {
            ?>
            <button type="button" onclick="downexcel('results')">
                <?php
                echo HTMLHelper::_(
                    'image',
                    'media/com_sportsmanagement/jl_images/excel.png',
                    Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_EXCEL'),
                    ['width' => 40]
                );
                ?> EXCEL
            </button>
            <?php
        }
    }
    ?>

    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>" method="post">
        <input type="hidden" name="limitstart" value=""/>
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($input->getCmd('view', 'results'), ENT_QUOTES, 'UTF-8'); ?>"/>
        <input type="hidden" name="option" value="<?php echo htmlspecialchars($input->getCmd('option', 'com_sportsmanagement'), ENT_QUOTES, 'UTF-8'); ?>"/>
        <input type="hidden" name="cfg_which_database" value="<?php echo $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0; ?>"/>
        <input type="hidden" name="p" value="<?php echo (int) ($this->project->id ?? $input->getInt('p', 0)); ?>"/>
        <input type="hidden" name="r" value="<?php echo (int) ($this->roundid ?? $input->getInt('r', 0)); ?>"/>
        <input type="hidden" name="division" value="<?php echo $input->getInt('division', 0); ?>"/>

        <?php if ($this->view === 'results') { ?>
            <div class="row">
                <div class="display-limit col-lg-2 col-md-2 col-sm-2 col-xs-2">
                    <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">
                    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                </div>
                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">
                    <p class="counter"><?php echo $this->pagination->getResultsCounter(); ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            </div>
        <?php } ?>

        <?php
        if (count($this->matches) > 0) {
            switch ((int) $this->config['result_style']) {
                case 4:
                    echo $this->loadTemplate('results_style_dfcday');
                    break;
                case 3:
                    echo $this->loadTemplate('results_style3');
                    break;
                default:
                    echo $this->loadTemplate('results_style0');
                    break;
            }
        }
        ?>
    </form>
</div>

<?php
if ($this->config['show_dnp_teams']) {
    echo $this->loadTemplate('freeteams');
}
