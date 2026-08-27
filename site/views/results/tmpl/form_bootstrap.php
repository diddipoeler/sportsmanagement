<?php
/**
 * SportsManagement bootstrap results edit form.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$input = $app->getInput();
$uri = Uri::getInstance();
$databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = (int) ($this->project->season_id ?? $input->getInt('s', 0));
$projectRoute = (string) ($this->project->slug ?? $this->project->id ?? $input->getInt('p', 0));
$roundRoute = (string) ($this->roundid ?? $input->getInt('r', 0));
$divisionId = $input->getInt('division', 0);
$mode = $input->getInt('mode', 0);
$order = $input->getInt('order', 0);

$this->divclass = '';
$this->divclassrest = '';
$this->columns = 12;
?>
<div class="container-fluid">
    <div class="row-fluid">
        <?php
        if ($this->overallconfig['use_bootstrap_version']) {
            $this->divclass = 'col-xs-6 col-sm-6 col-md-6 col-lg-6';
        } else {
            $this->divclass = 'span6';
        }

        if ($this->roundid > 0) {
            ?>
            <div class="<?php echo $this->divclass; ?>">
                <?php
                sportsmanagementHelperHtml::showMatchdaysTitle(
                    Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS'),
                    $this->roundid,
                    $this->config
                );

                if ($this->showediticon) {
                    $routeparameter = [
                        'cfg_which_database' => $databaseSelector,
                        's' => $seasonId,
                        'p' => $projectRoute,
                        'r' => $roundRoute,
                        'division' => $divisionId,
                        'mode' => $mode,
                        'order' => $order,
                        'layout' => '',
                    ];
                    $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_CLOSE_EDIT_RESULTS');
                    $desc = HTMLHelper::image(
                        'media/com_sportsmanagement/jl_images/edit_exit.png',
                        $imgTitle,
                        ['title' => $imgTitle]
                    );
                    echo '&nbsp;' . HTMLHelper::link($link, $desc);
                }
                ?>
            </div>
            <?php
        }
        ?>
        <div class="<?php echo $this->divclass; ?>">
            <?php
            echo RoundPaginationHelper::selectNavigation(
                $this->project,
                $databaseSelector,
                $seasonId,
                'form_bootstrap'
            );
            ?>
        </div>
    </div>

    <form name="adminForm" id="adminForm" method="post" action="<?php echo $uri->toString(); ?>">
        <div class="row-fluid">
            <?php
            if ($this->overallconfig['use_bootstrap_version']) {
                $columnWidth = max(1, (int) round(12 / $this->columns));
                $this->divclass = 'col-xs-' . $columnWidth
                    . ' col-sm-' . $columnWidth
                    . ' col-md-' . $columnWidth
                    . ' col-lg-' . $columnWidth;
                $this->divclassrest = 'col-xs-3 col-sm-3 col-md-3 col-lg-3';
            } else {
                $this->divclass = 'span' . max(1, (int) round(12 / $this->columns));
                $this->divclassrest = 'span3';
            }
            ?>
            <div class="<?php echo $this->divclass; ?>">
                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->matches); ?>);"/>
            </div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_MATCHNR'); ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DATE'); ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_TIME'); ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT'); ?></div>
            <div class="<?php echo $this->divclass; ?>">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS'); ?><br/>
                <?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS'); ?><br/>
                <?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE'); ?><br/>
            </div>
            <div class="<?php echo $this->divclassrest; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PUBLISHED'); ?></div>
        </div>

        <?php
        $i = 0;
        foreach ($this->matches as $match) {
            if (isset($match->allowed) && $match->allowed) {
                $this->game = $match;
                $this->i = $i;
                echo $this->loadTemplate('row');
            }
            $i++;
        }
        ?>

        <br/>
        <input type="hidden" name="option" value="com_sportsmanagement"/>
        <input type="hidden" name="view" value="results"/>
        <input type="hidden" name="cfg_which_database" value="<?php echo $databaseSelector; ?>"/>
        <input type="hidden" name="s" value="<?php echo $seasonId; ?>"/>
        <input type="hidden" name="p" value="<?php echo (int) ($this->project->id ?? 0); ?>"/>
        <input type="hidden" name="r" value="<?php echo $roundRoute; ?>"/>
        <input type="hidden" name="division" value="<?php echo $divisionId; ?>"/>
        <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
        <input type="hidden" name="order" value="<?php echo $order; ?>"/>
        <input type="hidden" name="layout" value="form_bootstrap"/>
        <input type="hidden" name="task" value="results.saveshort"/>
        <input type="hidden" name="sel_r" value="<?php echo $roundRoute; ?>"/>
        <input type="hidden" name="Itemid" value="<?php echo $input->getInt('Itemid', 1); ?>"/>
        <input type="hidden" name="boxchecked" value="0" id="boxchecked"/>
        <input type="hidden" name="checkmycontainers" value="0" id="checkmycontainers"/>
        <input type="hidden" name="save_data" value="1" class="button"/>
        <input type="submit" name="save" value="<?php echo Text::_('JSAVE'); ?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
