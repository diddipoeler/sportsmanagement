<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage specialextensions
 * @file       default_4.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <div class="row">
		<?php 
/**
if (!empty($this->sidebar))
		:
		?>
        <div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
        </div>
        <div class="col-md-8">
			<?php else

			:
			?>
            <div class="col-md-10">
				<?php endif; 
*/
?>

<?php
require(JPATH_COMPONENT_ADMINISTRATOR . '/views/listheader/tmpl/default_4_start_menu.php');   
?>		    
		              <div class="col-md-10">
                <div id="dashboard-iconss" class="dashboard-icons">
					<?php
					foreach ($this->Extensions as $key => $value)
					{
						$logo = "components/com_sportsmanagement/assets/icons/" . Text::_($value) . '.png';

						if (!file_exists($logo))
						{
							$logo = Uri::root() . 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
						}
						?>
                        <a class="btn btn-jsm-dash"
                           href="index.php?option=com_sportsmanagement&view=<?php echo Text::_($value) ?>">
                            <img src="<?php echo $logo ?>" width="125" alt="<?php echo Text::_($value) ?>"/><br/>
                            <span><?php echo Text::_($value) ?></span>
                        </a>
						<?php
					}
					?>
                </div>
            </div>
            <div class="col-md-2">
				<?php sportsmanagementHelper::jsminfo(); ?>
            </div>
        </div>
    </div>
<?PHP

echo $this->loadTemplate('footer');

