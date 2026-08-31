<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$sportTypeIcons = [
    'soccer' => [
        ['dfbnetimport.png', 'index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT')],
        ['dfbschluessel.png', 'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY')],
        ['lmoimport.png', 'index.php?option=com_sportsmanagement&view=jlextlmoimports', Text::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT')],
        ['profleagueimport.png', 'index.php?option=com_sportsmanagement&view=jlextprofleagimport', Text::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT')],
    ],
    'basketball' => [
        ['dbbimport.png', 'index.php?option=com_sportsmanagement&view=jlextdbbimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT')],
    ],
    'handball' => [
        ['sisimport.png', 'index.php?option=com_sportsmanagement&view=jlextsisimport', Text::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT')],
    ],
];
?>
<div id="cpanel" class="clearfix">
    <?php foreach ($this->sporttypes as $sportType) : ?>
        <?php foreach ($sportTypeIcons[(string) $sportType] ?? [] as $iconInfo) : ?>
            <?php echo $this->addIcon($iconInfo[0], $iconInfo[1], $iconInfo[2]); ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
