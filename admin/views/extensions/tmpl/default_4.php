<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage extensions
 * @file       default_4.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <div class="row">
		<?php if (!empty($this->sidebar))
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
				<?php endif; ?>
                <div id="dashboard-iconss" class="dashboard-icons">
					<?php
					if ($this->sporttypes != null)
                    {
                        foreach ($this->sporttypes as $key => $value)
                        {
                            switch ($value)
                            {
                                case 'soccer':
                                    ?>
                                    <a class="btn btn-jsm-dash"
                                    href="index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport">
                                        <img src="components/com_sportsmanagement/assets/icons/dfbnetimport.png"
                                            alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT') ?>"/><br/>
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT') ?></span>
                                    </a>
                                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=jlextdfbkeyimport">
                                        <img src="components/com_sportsmanagement/assets/icons/dfbschluessel.png"
                                            alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY') ?>"/><br/>
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY') ?></span>
                                    </a>
                                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=jlextlmoimports">
                                        <img src="components/com_sportsmanagement/assets/icons/lmoimport.png"
                                            alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT') ?>"/><br/>
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT') ?></span>
                                    </a>
                                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=jlextprofleagimport">
                                        <img src="components/com_sportsmanagement/assets/icons/profleagueimport.png"
                                            alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT') ?>"/><br/>
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT') ?></span>
                                    </a>
                                    <?PHP
                                    break;
                                case 'basketball':
                                    ?>
                                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=jlextdbbimport">
                                        <img src="components/com_sportsmanagement/assets/icons/dbbimport.png"
                                            alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT') ?>"/><br/>
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT') ?></span>
                                    </a>
                                    <?PHP
                                    break;
                                case 'handball':
                                    ?>
                                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=jlextsisimport">
                                        <img src="components/com_sportsmanagement/assets/icons/sisimport.png"
                                            alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT') ?>"/><br/>
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT') ?></span>
                                    </a>
                                    <?PHP
                                    break;
                                default:
                                    break;
                            }
                        }
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
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
