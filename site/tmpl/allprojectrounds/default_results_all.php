<?php
/** SportsManagement all project rounds result layout for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->escape($this->divclassrow); ?>" id="allprojectrounds-results">
    <?php if (!empty($this->config['show_button_download_pdf'])) : ?>
        <button type="button" class="btn" onclick="downpdf('allprojectrounds');">
            <?php
            echo HTMLHelper::image(
                'media/com_sportsmanagement/jl_images/pdf.png',
                Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_PDF'),
                ['width' => 40]
            );
            ?> PDF
        </button>
    <?php endif; ?>

    <?php if (!empty($this->config['show_button_download_excel'])) : ?>
        <button type="button" class="btn" onclick="downexcel('allprojectrounds');">
            <?php
            echo HTMLHelper::image(
                'media/com_sportsmanagement/jl_images/excel.png',
                Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_EXCEL'),
                ['width' => 40]
            );
            ?> EXCEL
        </button>
    <?php endif; ?>

    <table class="<?php echo $this->escape($this->tableclass); ?>" id="allprojectrounds">
        <tr>
            <td><?php echo $this->content; ?></td>
        </tr>
    </table>
</div>
