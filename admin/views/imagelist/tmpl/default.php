<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

//JHtml::_('script', 'media/popup-imagemanager.min.js', array('version' => 'auto', 'relative' => true));
//JHtml::_('stylesheet', 'media/popup-imagemanager.css', array('version' => 'auto', 'relative' => true));


//echo '<pre>'.print_r($this->images,true).'</pre>';
?>
<?php if ( count($this->images) > 0 ) : ?>
	<ul class="manager thumbnails">
		<?php for ($i = 0, $n = count($this->images); $i < $n; $i++) :
			$this->setImage($i);
			include( dirname(__FILE__) . '/default_image.php');
		endfor; ?>
	</ul>
<?php else : ?>
	<div id="media-noimages">
		<div class="alert alert-info"><?php echo JText::_('COM_MEDIA_NO_IMAGES_FOUND'); ?></div>
	</div>
<?php endif; ?>

