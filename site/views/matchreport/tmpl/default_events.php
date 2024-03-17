<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_events.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<!-- START of match events -->
<div class="<?php echo $this->divclassrow; ?> " id="matchreport-events">
    <h2>
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS');
		?>
    </h2>

	<?php
	if ($this->config['show_timeline'] && !$this->config['show_timeline_under_results'])
	{
		echo $this->loadTemplate('timeline');
	}
	?>
    <div class="row ">
		<?php
		foreach ($this->eventtypes as $event)
		{
			?>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 d-flex justify-content-center" style="">
					<?php echo HTMLHelper::_('image', $event->icon, Text::_($event->icon), null) . Text::_($event->name); ?>
            </div>
            

                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-end" style="">
                    <dl>
						<?php echo $this->showEvents($event->id, $this->match->projectteam1_id); ?>
                    </dl>
                </div>
                
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2" style="">
                  </div>
                
               <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
                    <dl>
						<?php echo $this->showEvents($event->id, $this->match->projectteam2_id); ?>
                    </dl>
                </div>

			<?php
		}
		?>
    </div>
    <!-- END of match events -->
</div>
<br/>
