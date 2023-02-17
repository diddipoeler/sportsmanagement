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
                // Define icon and link information for each sport type
                $sportType = [
                    'soccer' => [
                        ['dfbnetimport.png', 'index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport', 'COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT'],
                        ['dfbschluessel.png', 'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport', 'COM_SPORTSMANAGEMENT_EXT_DFBKEY'],
                        ['lmoimport.png', 'index.php?option=com_sportsmanagement&view=jlextlmoimports', 'COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'],
                        ['profleagueimport.png', 'index.php?option=com_sportsmanagement&view=jlextprofleagimport', 'COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT'],
                    ],
                    'basketball' => [
                        ['dbbimport.png', 'index.php?option=com_sportsmanagement&view=jlextdbbimport', 'COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'],
                    ],
                    'handball' => [
                        ['sisimport.png', 'index.php?option=com_sportsmanagement&view=jlextsisimport', 'COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'],
                    ],
                ];

                // Loop through each sport type and output the corresponding icons and links
                foreach ($this->sporttypes as $key => $value) {
                    if (array_key_exists($value, $sportType)) {
                        foreach ($sportType[$value] as $sportData) {
                            ?>
                            <a class="btn btn-jsm-dash" href="<?php echo $sportData[1]; ?>">
                                <img src="components/com_sportsmanagement/assets/icons/<?php echo $sportData[0]; ?>"
                                    alt="<?php echo Text::_($sportData[2]); ?>" /><br />
                                <span>
                                    <?php echo Text::_($sportData[2]); ?>
                                </span>
                            </a>
                        <?php
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

echo $this->loadTemplate('footer');