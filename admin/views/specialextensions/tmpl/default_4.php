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
						<nav class="quick-icons px-3 py-3">
							<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(180px,1fr));">
								<li class="quickicon quickicon-single">
									<a title="<?php echo Text::_($value) ?>"
									href="index.php?option=com_sportsmanagement&view=<?php echo Text::_($value) ?>">
										<div class="quickicon-icon">
											<img src="<?php echo $logo ?>" 
											height="48px" alt="<?php echo Text::_($value) ?>"/>
										</div>
										<div class="quickicon-name d-flex align-items-end">
											<?php echo Text::_($value) ?>
										</div>
									</a>
								</li>
							</ul>
						</nav>
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

