<?php
/** SportsManagement playground picture for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Joomla\CMS\Language\Text;

$picture = trim((string) ($this->playground->picture ?? ''));

if ($picture === '') {
    return;
}

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_PICTURE')];
echo $this->loadTemplate('jsm_notes');

$pictureUrl = preg_match('#^https?://#i', $picture)
    ? $picture
    : rtrim((string) COM_SPORTSMANAGEMENT_PICTURE_SERVER, '/') . '/' . ltrim($picture, '/');
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> table-responsive" id="playground_picture">
    <?php
    echo ModalImageHelper::render(
        'playground' . (int) $this->playground->id,
        $pictureUrl,
        (string) ($this->playground->name ?? ''),
        (int) ($this->config['playground_picture_width'] ?? 150),
        '',
        $this->modalwidth,
        $this->modalheight,
        (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
    );
    ?>
</div>
