<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_preview.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\Registry\Registry;
use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

?>
<!-- START of match preview -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="nextmatch-preview">
<div class="panel-group" id="accordionnextmatch">
<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordionnextmatch" href="#nextpreview"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'); ?></a>
                </h4>
            </div>
            <div id="nextpreview" class="panel-collapse collapse">
                <div class="panel-body">
<?php
/**
 * workaround to support {jcomments (off|lock)} in match preview
 * no comments are shown if {jcomments (off|lock)} is found in the match preview
 */
$commentsDisabled = 0;

if (!empty($this->match->preview) && preg_match('/{jcomments\s+(off|lock)}/is', $this->match->preview)) {
    $commentsDisabled = 1;
}

if (!empty($this->match->preview)) {
    ?>
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'); ?></h2>
    <table class="table">
        <tr>
            <td><?php
                $preview = $this->match->preview;
                $preview = HTMLHelper::_('content.prepare', $preview);

                if ($commentsDisabled) {
                    $preview = preg_replace('#{jcomments\s+(off|lock)}#is', '', $preview);
                }

                echo $preview;
                ?>
            </td>
        </tr>
    </table>
    <!-- END of match preview -->

    <?php
}
if (version_compare(JSM_JVERSION, '4', 'eq')) {
    //$dispatcher = Dispatcher::getInstance();
} else {
    $dispatcher = JDispatcher::getInstance();
}
$comments = '';
if (file_exists(JPATH_ROOT . '/components/com_jcomments/classes/config.php')) {
    require_once JPATH_ROOT . '/components/com_jcomments/classes/config.php';
    require_once JPATH_ROOT . '/components/com_jcomments/jcomments.class.php';
    require_once JPATH_ROOT . '/components/com_jcomments/models/jcomments.php';
}

/**
 * load sportsmanagement comments plugin files
 */
PluginHelper::importPlugin('content', 'sportsmanagement_comments');

/**
 * get sportsmanagement comments plugin params
 */
$plugin = PluginHelper::getPlugin('content', 'sportsmanagement_comments');

if (is_object($plugin)) {
    $pluginParams = new Registry($plugin->params);
} else {
    $pluginParams = new Registry('');
}
$separate_comments = $pluginParams->get('separate_comments', 0);

if ($separate_comments) {
    /**
     * Comments integration trigger when separate_comments in plugin is set to yes/1
     */
    if (Factory::getApplication()->triggerEvent('onNextMatchComments', array(&$this->match, $this->teams[0]->name . ' - ' . $this->teams[1]->name, &$comments))) {
        echo $comments;
    }
} else {
    /**
     * Comments integration trigger when separate_comments in plugin is set to no/0
     */
    if (Factory::getApplication()->triggerEvent('onMatchComments', array(&$this->match, $this->teams[0]->name . ' - ' . $this->teams[1]->name, &$comments))) {
        echo $comments;
    }
}
?>
</div>
</div>
</div>
</div>
</div>