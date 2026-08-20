<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;

$massadd = $this->app->getInput()->getInt('massadd', 0);
$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
    echo $this->loadTemplate('debug');
}
?>
<div id="alt_decision_enter" style="display:<?php echo $massadd === 0 ? 'none' : 'block'; ?>">
    <?php echo $this->loadTemplate('massadd'); ?>
</div>
<?php
switch ((string) ($this->projectws->sports_type_name ?? '')) {
    case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
        echo $this->loadTemplate('matches_small_bore_rifle');
        break;

    default:
        echo $this->loadTemplate('matches');
        if (ComponentHelper::getParams($this->option)->get('show_edit_matches_matrix')) {
            echo $this->loadTemplate('matrix');
        }
        break;
}

echo $this->loadTemplate('footer');
