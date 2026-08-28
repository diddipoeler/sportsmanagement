<?php
/** SportsManagement referees template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

if (!isset($this->config['show_referees'])) {
    $this->config['show_referees'] = 1;
}
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="referees">
    <?php
    if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
        echo $this->loadTemplate('debug');
    }

    echo $this->loadTemplate('projectheading');

    if (!empty($this->config['show_sectionheader']) && $this->headertitle !== '') :
        ?>
        <div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
            <table class="table">
                <tr>
                    <td class="contentheading"><?php echo $this->escape($this->headertitle); ?></td>
                </tr>
            </table>
        </div>
        <?php
    endif;

    if (!empty($this->config['show_referees'])) {
        echo $this->loadTemplate('referees');
    }

    echo $this->loadTemplate('jsminfo');
    ?>
</div>
