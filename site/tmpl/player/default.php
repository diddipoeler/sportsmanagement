<?php
/**
 * Native Joomla 5/6 player default layout.
 *
 * @package     SportsManagement
 * @subpackage  Site
 * @since       5.6.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$templatesToLoad = ['globalviews'];
\sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$containerClass = trim((string) ($this->divclasscontainer ?? ''));
$config = is_array($this->config ?? null) ? $this->config : [];

if (isset($this->person)) :
    ?>
    <div class="<?php echo $this->escape($containerClass); ?>" id="defaultplayer">
        <?php
        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            echo $this->loadTemplate('debug');
        }

        echo $this->loadTemplate('projectheading');

        if (!empty($config['show_sectionheader'])) {
            echo $this->loadTemplate('sectionheader');
        }

        $this->output = [];
        echo $this->loadTemplate('info');

        if (!empty($config['show_extra_fields'])) {
            $this->output[(int) ($config['show_order_extra_fields'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS',
                'template' => 'extrafields',
            ];
        }

        if (!empty($config['show_extended']) && !empty($this->hasExtendedData)) {
            $this->output[(int) ($config['show_order_extended'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_TABS_EXTENDED',
                'template' => 'extended',
            ];
        }

        if (!empty($config['show_plstatus']) && !empty($this->hasStatus)) {
            $this->output[(int) ($config['show_order_plstatus'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_PERSON_STATUS',
                'template' => 'status',
            ];
        }

        if (!empty($config['show_description']) && !empty($this->hasDescription)) {
            $this->output[(int) ($config['show_order_description'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_PERSON_INFO',
                'template' => 'description',
            ];
        }

        if (!empty($config['show_gameshistory']) && !empty($this->games)) {
            $this->output[(int) ($config['show_order_gameshistory'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY',
                'template' => 'gameshistory',
            ];
        }

        if (!empty($config['show_plstats'])) {
            $this->output[(int) ($config['show_order_plstats'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS',
                'template' => 'playerstats',
            ];
        }

        if (!empty($config['show_plcareer']) && !empty($this->historyPlayer)) {
            $this->output[(int) ($config['show_order_plcareer'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER',
                'template' => 'playercareer',
            ];
        }

        if (!empty($config['show_stcareer']) && !empty($this->historyPlayerStaff)) {
            $this->output[(int) ($config['show_order_stcareer'] ?? 0)] = [
                'text' => 'COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER',
                'template' => 'playerstaffcareer',
            ];
        }

        ksort($this->output);
        echo $this->loadTemplate($config['show_players_layout'] ?? 'no_tabs');
        echo $this->loadTemplate('jsminfo');
        ?>
    </div>
    <?php
else :
    ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><?php echo Text::_('COM_SPORTSMANAGEMENT_ERROR'); ?></h4>
        <?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NO_SELECTED'); ?>
    </div>
    <?php
endif;
