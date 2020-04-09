<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
 * @file       default_ranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die('Restricted access'); ?>

<div class="<?php echo $this->divclassrow; ?> table-responsive">
    <!-- content -->

    <div class="panel-group" id="projectnames">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#projectnames"
                       href="#viewprojectnames"><?php echo Text::_('COM_SPORTSMANAGEMENT_ALLPROJECTS_PAGE_TITLE'); ?></a>
                </h4>
            </div>
            <div id="viewprojectnames" class="panel-collapse collapse">
                <div class="panel-body">
                    <table class="table">
						<?php

						foreach ($this->projectnames as $value)
						{
							$createroute = array("option"             => "com_sportsmanagement",
							                     "view"               => "ranking",
							                     "cfg_which_database" => 0,
							                     "s"                  => 0,
							                     "p"                  => $value->project_slug,
							                     "type"               => 0,
							                     "r"                  => 0,
							                     "from"               => 0,
							                     "to"                 => 0,
							                     "division"           => 0,);
							$query       = sportsmanagementHelperRoute::buildQuery($createroute);
							$link        = Route::_('index.php?' . $query, false);

							?>
                            <tr>
                                <td>
                                    <a href="<?PHP echo $link; ?>" class="btn btn-primary btn-lg btn-block"
                                       role="button">
										<?PHP
										echo Text::_($value->name);
										?>
                                    </a>
                                </td>
                            </tr>
							<?php
						}
						?>
                    </table>
                </div>
            </div>
        </div>

    </div>


	<?php
	foreach ($this->currentRanking as $division => $cu_rk)
	{
		if ($division)
		{
			?>
            <table class="table">
                <tr>
                    <td class="contentheading">
						<?php
						// Get the division name from the first team of the division
						foreach ($cu_rk as $ptid => $team)
						{
							echo $this->divisions[$division]->name;
							break;
						}
						?>
                    </td>
                </tr>
            </table>

            <table class="table">
				<?php
				foreach ($cu_rk as $ptid => $team)
				{
					break;
				}

				$this->division = $division;
				$this->current  = &$cu_rk;
				echo $this->loadTemplate('rankingrows');
				?>
            </table>
			<?php
		}
		else
		{
			?>
            <table class="table">
				<?php
				echo $this->loadTemplate('rankingheading');
				$this->division = $division;
				$this->current  = &$cu_rk;
				echo $this->loadTemplate('rankingrows');
				?>
            </table>
            <br/>
			<?php
		}
	}
	?>
</div>
<!-- ranking END -->



