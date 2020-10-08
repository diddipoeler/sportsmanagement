<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 *  Make sure that in case extensions are written for mentioned (common) views,
 *  that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if (isset($this->person))
{
	?>

    <!-- player anfang -->
    <div class="<?php echo $this->divclasscontainer; ?>" id="defaultplayer">
		<?php
		if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
		{
			echo $this->loadTemplate('debug');
		}

		echo $this->loadTemplate('projectheading');

		if ($this->config['show_sectionheader'])
		{
			echo $this->loadTemplate('sectionheader');
		}

		/** player view start */
		$this->output = array();
		echo $this->loadTemplate('info');

		if ($this->config['show_playfield'])
		{
			$this->output[intval($this->config['show_order_playfield'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD', 'template' => 'playfield');
		}

		if ($this->config['show_extra_fields'])
		{
			$this->output[intval($this->config['show_order_extra_fields'])] = array('text' => 'COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS', 'template' => 'extrafields');
		}

		if ($this->config['show_extended'] && $this->hasExtendedData)
		{
			$this->output[intval($this->config['show_order_extended'])] = array('text' => 'COM_SPORTSMANAGEMENT_TABS_EXTENDED', 'template' => 'extended');
		}

		if ($this->config['show_plstatus'] && $this->hasStatus)
		{
			$this->output[intval($this->config['show_order_plstatus'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_STATUS', 'template' => 'status');
		}

		if ($this->config['show_description'] && !empty($this->hasDescription))
		{
			$this->output[intval($this->config['show_order_description'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_INFO', 'template' => 'description');
		}

		if ($this->config['show_gameshistory'] && count($this->games))
		{
			$this->output[intval($this->config['show_order_gameshistory'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY', 'template' => 'gameshistory');
		}

		if ($this->config['show_plstats'])
		{
			$this->output[intval($this->config['show_order_plstats'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS', 'template' => 'playerstats');
		}

		if ($this->config['show_plcareer'] && count($this->historyPlayer) > 0)
		{
			$this->output[intval($this->config['show_order_plcareer'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER', 'template' => 'playercareer');
		}

		if ($this->config['show_stcareer'] && count($this->historyPlayerStaff) > 0)
		{
			$this->output[intval($this->config['show_order_stcareer'])] = array('text' => 'COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER', 'template' => 'playerstaffcareer');
		}

		/**
		 * das array muss noch sortiert werden, sonst macht
		 * die user vorgabe keinen sinn
		 */
		ksort($this->output);
		echo $this->loadTemplate($this->config['show_players_layout']);
		echo $this->loadTemplate('jsminfo');
		?>

		<?PHP

		?>
    </div>
    <!-- player ende -->
    </div>
	<?php
}
else
{
	?>
    <div class="alert alert-error">
        <h4>
			<?php
			echo Text::_('COM_SPORTSMANAGEMENT_ERROR');
			?>
        </h4>
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NO_SELECTED');
		?>
    </div>
	<?php
}
