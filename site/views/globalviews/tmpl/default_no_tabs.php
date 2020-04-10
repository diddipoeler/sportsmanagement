<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_no_tabs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

?>
<div class="<?php echo $this->divclassrow; ?>" id="no_tabs">
    <div class="col-xs-<?php echo $this->config['extended_cols']; ?> col-sm-<?php echo $this->config['extended_cols']; ?> col-md-<?php echo $this->config['extended_cols']; ?> col-lg-<?php echo $this->config['extended_cols']; ?>">
		<?php
		$view = Factory::getApplication()->input->getCmd('view');

		foreach ($this->output as $key => $templ)
		{
			switch ($view)
			{
				case 'player':
					$template = $templ['template'];
					$text     = $templ['text'];
					break;
				default:
					$template = $templ;
					$text     = $key;
					break;
			}

			echo $this->loadTemplate($template);
		}
		?>
    </div>
</div>
