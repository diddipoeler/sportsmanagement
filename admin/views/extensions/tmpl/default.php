<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage extensions
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<table width="100%" border="0">
    <tr>
        <td width="100%" valign="top">
            <div id="cpanel">
                <?php
              
                foreach ( $this->sporttypes as $key => $value )
                {
                    switch ($value)
                    {
                    case 'soccer':
                        echo $this->addIcon('dfbnetimport.png', 'index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT'));
                        echo $this->addIcon('dfbschluessel.png', 'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY'));
                        echo $this->addIcon('lmoimport.png', 'index.php?option=com_sportsmanagement&view=jlextlmoimports', Text::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'));
                        echo $this->addIcon('profleagueimport.png', 'index.php?option=com_sportsmanagement&view=jlextprofleagimport', Text::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT'));
                        break;
                    case 'basketball':
                        echo $this->addIcon('dbbimport.png', 'index.php?option=com_sportsmanagement&view=jlextdbbimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'));
                        break;
                    case 'handball':
                        echo $this->addIcon('sisimport.png', 'index.php?option=com_sportsmanagement&view=jlextsisimport', Text::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'));
                        break;
                    default:
                        break;
                    }
                }
                ?>

            </div>
        </td>
    </tr>
</table>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 
