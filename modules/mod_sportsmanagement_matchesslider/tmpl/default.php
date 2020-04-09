<?PHP
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_matchesslider
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

?>

<script type="text/javascript">
    (function ($) {
        $(function () { //on DOM ready
            $("#scroller").simplyScroll({
                customClass: 'custom',
                direction: '<?php echo $params->get('slide_direction'); ?>',
                pauseOnHover: false,
                frameRate: 20,
                speed: 2
            });
        });
    })(jQuery);
</script>

<div id="scroller" class="row">


	<?PHP
	foreach ($slidermatches as $match)
	{
		?>
        <div class="section">
            <div class="hp-highlight">
                <div class="feature-headline">
                    <h1>
                        <a href="<?PHP echo $link; ?>" title="">
							<?PHP
							// Echo $match->match_date;
							echo HTMLHelper::_('date', $match->match_date, $params->get('dateformat'), null);
							echo ' ';
							echo HTMLHelper::_('date', $match->match_date, $params->get('timeformat'), null);
							?>
                        </a>
                    </h1>
                    <p style="text-align: center;">
						<?PHP
						echo '<img style="float: left;" src="' . $module->picture_server . $match->logohome . '" alt="' . $match->teamhome . '"  width="' . $params->get('xsize') . '" title="' . $match->teamhome . '" ' . $match->teamhome . ' />';
						echo '' . $match->team1_result;
						echo ' - ';
						echo $match->team2_result . '';
						echo '<img style="float: right;" src="' . $module->picture_server . $match->logoaway . '" alt="' . $match->teamaway . '" width="' . $params->get('xsize') . '" title="' . $match->teamaway . '" ' . $match->teamaway . ' />';
						?>
                    </p>

                    <p style="text-align: center;">
						<?PHP
						echo $match->teamhome;
						echo ' - ';
						echo $match->teamaway;
						?>
                    </p>

                </div>
            </div>
        </div>
		<?PHP
	}
	?>

</div>  
