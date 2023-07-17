<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage results
 * @file       form.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$user = Factory::getUser();
$saveshortall = $user->authorise('editmatch.saveshortall', 'com_sportsmanagement');
$saveshortresults = $user->authorise('editmatch.saveshortresults', 'com_sportsmanagement');

if (version_compare(JSM_JVERSION, '4', 'eq'))
{
	$uri = Uri::getInstance();
}
else
{
	$uri = Factory::getURI();
}

if ($this->overallconfig['use_jquery_modal'])
{
	?>
    <!--
	<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/css/bootstrap-dialog.min.css" rel="stylesheet" type="text/css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.9/js/bootstrap-dialog.min.js"></script>
	-->
	<?php
}

if ($this->overallconfig['use_squeezebox_modal'])
{
}

if (!$this->showediticon)
{
	Factory::getApplication()->redirect(str_ireplace('layout=form', '', $uri->toString()), Text::_('ALERTNOTAUTH'));
}

$document = Factory::getDocument();
/** welche joomla version */
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
}
else
{
}

?>
<script>

</script>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultresultsform" style="overflow:auto;">
    <!-- edit results start -->
    <table class="table"  id="results-tmpl-form-contentheading">
        <tr>
            <td class="contentheading">
				<?php
				if ($this->roundid > 0)
				{
					sportsmanagementHelperHtml::showMatchdaysTitle(Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS'), $this->roundid, $this->config);


					if ($this->showediticon) // Needed to check if the user is still allowed to get into the match edit
					{
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = sportsmanagementModelProject::$cfg_which_database;
						$routeparameter['s']                  = sportsmanagementModelProject::$seasonid;
						$routeparameter['p']                  = sportsmanagementModelProject::$projectslug;
						$routeparameter['r']                  = sportsmanagementModelProject::$roundslug;
						$routeparameter['division']           = sportsmanagementModelResults::$divisionid;
						$routeparameter['mode']               = sportsmanagementModelResults::$mode;
						$routeparameter['order']              = sportsmanagementModelResults::$order;
						$routeparameter['layout']             = '';
						$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

						$imgTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_CLOSE_EDIT_RESULTS');
						$desc     = HTMLHelper::image('media/com_sportsmanagement/jl_images/edit_exit.png', $imgTitle, array(' title' => $imgTitle));
						echo '&nbsp;';
						echo HTMLHelper::link($link, $desc);
					}
				}
				?>
            </td>
            <td>
				<?php
				echo sportsmanagementHelperHtml::getRoundSelectNavigation(true, sportsmanagementModelProject::$cfg_which_database);
				?>
            </td>
        </tr>
    </table>
    <form name="adminForm" id="adminForm" method="post" action="<?php echo $uri->toString(); ?>">
        <table class="<?php echo $this->config['table_class']; ?>" id="results-tmpl-form">
            <!-- Main START -->
			<?php
			if (count($this->matches) > 0)
			{
				$colspan = ($this->project->allow_add_time) ? 15 : 14;
				?>
                <thead>
                <tr class="">
                    <th width="20" style="vertical-align: top; ">
                        <input type="checkbox" name="toggle" value=""
                               onclick="checkAll(<?php echo count($this->matches); ?>);"/>
                    </th>
                    <th width="" style="vertical-align: top; "></th>
                    <th style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></th>
					<?php
					if ($this->project->project_type == 'DIVISIONS_LEAGUE')
					{
						$colspan++;
						?>
                        <th style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DIVISION'); ?></th>
						<?php
					}
					?>

					<?php
					if ($this->config['show_edit_match_number'])
					{
						?>
                        <th width="20"
                            style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_MATCHNR'); ?></th>
						<?php
					}


					if ($this->config['show_edit_match_date'])
					{
						?>
                        <th class="title" class="nowrap"
                            style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DATE'); ?></th>
						<?php
					}


					if ($this->config['show_edit_match_time'])
					{
						?>
                        <th class="title" class="nowrap"
                            style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_TIME'); ?></th>
						<?php
					}


					if ($this->config['show_edit_match_time_present'])
					{
						?>
                        <th class="title" class="nowrap"
                            style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PRESENT'); ?></th>
						<?php
					}
					?>
                    <th colspan="2" class="title" class="nowrap"
                        style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></th>
                    <th colspan="2" class="title" class="nowrap"
                        style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></th>
					<?php
					if ($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART')
					{
						?>
                        <th style="text-align: center; vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT_DART'); ?></th>
						<?php
					}
					else
					{
						?>
                        <th style="text-align: center; vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT'); ?></th>
						<?php
					}


					if ($this->project->allow_add_time)
					{
						?>
                        <th style="text-align:center; vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT_TYPE'); ?></th>
						<?php
					}
					?>

					<?php
					if ($this->config['show_edit_match_events'])
					{
						if ($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART')
						{
							?>
                            <th class="title" class="nowrap"
                                style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_DARTS'); ?></th>
							<?php
						}
						else
						{
							?>
                            <th class="title" class="nowrap"
                                style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS'); ?></th>
							<?php
						}


						?>


						<?php
					}


					if ($this->config['show_edit_match_statistic'])
					{
						?>
                        <th class="title" class="nowrap"
                            style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS'); ?></th>
						<?php
					}


					if ($this->config['show_edit_match_referees'])
					{
						?>
                        <th class="title" class="nowrap"
                            style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE'); ?></th>
						<?php
					}
					?>
                    <th width="1%" class="nowrap"
                        style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PUBLISHED'); ?></th>
                </tr>
                </thead>
                <!-- Start of the matches for the selected round -->
				<?php
				$k = 0;
				$i = 0;

				foreach ($this->matches as $match)
				{
					if (isset($match->allowed) && $match->allowed)
					{
						$this->game = $match;
						$this->i    = $i;
						/** eingabe laden */
						echo $this->loadTemplate('row');
					}

					$k = 1 - $k;
					$i++;
				}
			}
			?>
        </table>
        <br/>
        <input type='hidden' name='option' value='com_sportsmanagement'/>
        <input type='hidden' name='view' value='results'/>
        <input type='hidden' name='cfg_which_database'
               value='<?php echo sportsmanagementModelProject::$cfg_which_database; ?>'/>
        <input type='hidden' name='s' value='<?php echo sportsmanagementModelProject::$seasonid; ?>'/>
        <input type='hidden' name='p' value='<?php echo sportsmanagementModelResults::$projectid; ?>'/>
        <input type='hidden' name='r' value='<?php echo sportsmanagementModelProject::$roundslug; ?>'/>
        <input type='hidden' name='divisionid' value='<?php echo sportsmanagementModelResults::$divisionid; ?>'/>
        <input type='hidden' name='mode' value='<?php echo sportsmanagementModelResults::$mode; ?>'/>
        <input type='hidden' name='order' value='<?php echo sportsmanagementModelResults::$order; ?>'/>
        <input type='hidden' name='layout' value='form'/>
        <input type='hidden' name='task' value='results.saveshort'/>
	    
	    <input type='hidden' name='saveshortall' value='<?php echo $saveshortall; ?>'/>
	    
        <input type='hidden' name='sel_r' value='<?php echo sportsmanagementModelProject::$roundslug; ?>'/>
        <input type='hidden' name='Itemid'
               value='<?php echo Factory::getApplication()->input->getInt('Itemid', 1, 'get'); ?>'/>
        <input type='hidden' name='boxchecked' value='0' id='boxchecked'/>
        <input type='hidden' name='checkmycontainers' value='0' id='checkmycontainers'/>
        <input type='hidden' name='save_data' value='1' class='button'/>
        <input type='submit' name='save' value='<?php echo Text::_('JSAVE'); ?>'/>
		<?php echo HTMLHelper::_('form.token'); ?>
        <!-- Main END -->
    </form>
</div>
<!-- matchdays pageNav -->
<br/>
<div class="display-limit">
	<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
	<?php echo $this->pagination->getLimitBox(); ?>
</div>
<div class="pagination">
    <p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
    </p>
    <p class="counter">
		<?php echo $this->pagination->getResultsCounter(); ?>
    </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<!-- matchdays pageNav END -->


<table class="table not-playing" width='96%' align='center' cellpadding='0' cellspacing='0'>
    <tr>
        <td style='text-align:center; '>
			<?php echo $this->showNotPlayingTeams($this->matches, $this->teams, $this->config, $this->favteams, $this->project); ?>
        </td>
    </tr>
</table>
