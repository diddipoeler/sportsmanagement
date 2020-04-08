<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       deafault_backbutton.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

if (isset($this->overallconfig['show_back_button']))
{
	?>
	<br />
	<?php
	if ($this->overallconfig['show_back_button'] == '1')
	{
		$alignStr = 'left';
	}
	else
	{
		$alignStr = 'right';
	}


	if ($this->overallconfig['show_back_button'] != '0')
	{
					?>
		   <div class="<?php echo $this->divclassrow;?>" style="text-align:<?php echo $alignStr; ?>; ">
  
		 <div class="btn back_button">
		  <a href='javascript:history.go(-1)'>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_BACKBUTTON_BACK');
				?>
		  </a>
		 </div>
		</div>
		<?php
	}
}
