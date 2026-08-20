<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;

$this->getDocument()->getWebAssetManager()->registerAndUseScript(
    'com_sportsmanagement.individualsport-admin',
    Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/individualsport-admin.js',
    [],
    ['defer' => true],
    ['core']
);
?>
<style>
    .subsequentdecision {
        background-color: #BBB;
    }
</style>
<div id="alt_decision_enter" style="display:<?php echo ($this->massadd == 0) ? 'none' : 'block'; ?>">
</div>

<?php
switch ($this->projectws->sports_type_name)
{
case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
echo $this->loadTemplate('matches_small_bore_rifle');
break;
default:
echo $this->loadTemplate('matches');
echo $this->loadTemplate('matrix');
break;
}
?>
