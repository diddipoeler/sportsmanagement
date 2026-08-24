<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage extensions
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<table width="100%" border="0">
	<tr>
		<td width="100%" valign="top">
			<div id="cpanel">
				<?php
				$sportTypeIcons = [
					'soccer' => [
						['dfbnetimport.png', 'index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT')],
						['dfbschluessel.png', 'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY')],
						['lmoimport.png', 'index.php?option=com_sportsmanagement&view=jlextlmoimports', Text::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT')],
						['profleagueimport.png', 'index.php?option=com_sportsmanagement&view=jlextprofleagimport', Text::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT')],
					],
					'basketball' => [
						['dbbimport.png', 'index.php?option=com_sportsmanagement&view=jlextdbbimport', Text::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT')],
					],
					'handball' => [
						['sisimport.png', 'index.php?option=com_sportsmanagement&view=jlextsisimport', Text::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT')],
					],
				];

				// Loop through each sport type and output the corresponding icons and links
				foreach ($this->sporttypes as $key => $value) {
					if (array_key_exists($value, $sportTypeIcons)) {
						foreach ($sportTypeIcons[$value] as $iconInfo) {
							echo $this->addIcon($iconInfo[0], $iconInfo[1], $iconInfo[2]);
						}
					}
				}
				?>
			</div>
		</td>
	</tr>
</table>
<?PHP

echo $this->loadTemplate('footer');