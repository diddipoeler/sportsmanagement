<?php
/** SportsManagement playground template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

if (!$this->playground) {
    return;
}
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="playground">
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

    if (!empty($this->config['show_playground'])) {
        echo $this->loadTemplate('playground');
    }

    if (!empty($this->config['show_extended'])) {
        echo $this->loadTemplate('extended');
    }

    if (!empty($this->config['show_picture'])) {
        echo $this->loadTemplate('picture');
    }

    if (
        !empty($this->playground->latitude)
        && !empty($this->playground->longitude)
        && !empty($this->config['show_maps'])
    ) {
        echo $this->loadTemplate('googlemap');
    }

    if (!empty($this->config['show_description'])) {
        echo $this->loadTemplate('description');
    }

    if (!empty($this->config['show_teams'])) {
        echo $this->loadTemplate('teams');
    }

    if (!empty($this->config['show_matches'])) {
        echo $this->loadTemplate('matches');
    }

    if (!empty($this->config['show_played_matches'])) {
        echo $this->loadTemplate('played_matches');
    }

    echo $this->loadTemplate('jsminfo');
    ?>
</div>
