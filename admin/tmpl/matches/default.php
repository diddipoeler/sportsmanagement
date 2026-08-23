<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

if (!$this->projectws) :
?>
    <div class="alert alert-warning" role="alert">
        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
    </div>
<?php
    return;
endif;

$massadd = $this->app->getInput()->getInt('massadd', 0) === 1;

if ($massadd) {
    echo $this->loadTemplate('massadd');
    return;
}

if ((string) ($this->projectws->sports_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION') {
    echo $this->loadTemplate('matches_small_bore_rifle');
    return;
}

// The Joomla 4 table is the currently maintained match row implementation and
// is used as the transition source while it is split into smaller native layouts.
echo $this->loadTemplate('4_matches');

if (ComponentHelper::getParams('com_sportsmanagement')->get('show_edit_matches_matrix')) {
    echo $this->loadTemplate('matrix');
}
