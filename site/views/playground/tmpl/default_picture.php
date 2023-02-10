<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_picture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>

<?php
if (($this->playground->picture))
{
	?>

<?php  
$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_PICTURE');
echo $this->loadTemplate('jsm_notes');
?>    
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_picture">
		<?php
		if (($this->playground->picture))
		{
			$picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->playground->picture;
		}
		else
		{
			$picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER . sportsmanagementHelper::getDefaultPlaceholder("team");
		}

		echo sportsmanagementHelperHtml::getBootstrapModalImage(
			'playground' . $this->playground->id,
			$picture,
			$this->playground->name,
			$this->config['playground_picture_width'],
			'',
			$this->modalwidth,
			$this->modalheight,
			$this->overallconfig['use_jquery_modal']
		)

		?>

    </div>
	<?php
}
