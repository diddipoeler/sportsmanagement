<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_allovereventsranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

//echo 'alloverevents <pre>'.print_r($this->alloverevents,true).'</pre>';
//echo 'overallevents <pre>'.print_r($this->overallevents,true).'</pre>';

?>
<div class="panel-group" id="allovereventsranking">
<?php
foreach ( $this->overallevents as $overallevents )
{
$width    = 20;
$height   = 20;
$type     = 4;
$imgTitle = Text::_($overallevents->name);
$icon     = sportsmanagementHelper::getPictureThumb($overallevents->icon, $imgTitle, $width, $height, $type);
?>
<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#allovereventsranking"
                       href="#<?php echo $overallevents->name; ?>"><?php echo $icon.' '.Text::_($overallevents->name); ?></a>
                </h4>
            </div>
            <div id="<?php echo $overallevents->name; ?>" class="panel-collapse collapse">
                <div class="panel-body">
                    <table class="table <?php echo $this->config['table_class'] ?>">
						<?php
						
						?>
                    </table>
                </div>
            </div>
        </div>







<?php
}
?>
</div>