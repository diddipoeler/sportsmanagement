<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_show_tabs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<div class="<?php echo $this->divclassrow; ?>" id="show_slider">
	<?php
	if (version_compare(JSM_JVERSION, '4', 'eq'))
	{
?>
<?php echo HTMLHelper::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'collapse0')); 
$i = 1;
$view   = Factory::getApplication()->input->getCmd('view');
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
echo HTMLHelper::_('bootstrap.addSlide', 'collapseTypes', Text::_($key), 'collapse' . ($i++));


echo $this->loadTemplate($template);
echo HTMLHelper::_('bootstrap.endSlide');
		}
echo HTMLHelper::_('bootstrap.endAccordion'); ?>
<?php
/*
		// Joomla! 4.0 code here
		$idxTab = 1;
		$view   = Factory::getApplication()->input->getCmd('view');

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

			if ($idxTab == 1)
			{
				echo HTMLHelper::_('bootstrap.startAccordion', $view, array('active' => 'slide' . $idxTab, 'parent' => $view));
			}

			echo HTMLHelper::_('bootstrap.addSlide', $view, Text::_($text), 'slide' . $idxTab);
			echo $this->loadTemplate($template);
			echo HTMLHelper::_('bootstrap.endSlide');
			$idxTab++;
		}

		echo HTMLHelper::_('bootstrap.endAccordion');
		*/
	}
    elseif (version_compare(JSM_JVERSION, '3', 'eq'))
	{
		// Joomla! 3.0 code here
		$idxTab = 1;
		$view   = Factory::getApplication()->input->getCmd('view');

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

			if ($idxTab == 1)
			{
				echo HTMLHelper::_('bootstrap.startAccordion', $view, array('active' => 'slide' . $idxTab, 'parent' => $view));
			}

			echo HTMLHelper::_('bootstrap.addSlide', $view, Text::_($text), 'slide' . $idxTab);
			echo $this->loadTemplate($template);
			echo HTMLHelper::_('bootstrap.endSlide');
			$idxTab++;
		}

		echo HTMLHelper::_('bootstrap.endAccordion');
	}
    elseif (version_compare(JSM_JVERSION, '2', 'eq'))
	{
		// Joomla! 2.5 code here
		?>

        <div class="panel-group" id="accordion-nextmatch">
			<?PHP
			foreach ($this->output as $key => $templ)
			{
				?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion-nextmatch"
                               href="#<?php echo $key; ?>"><?php echo Text::_($key); ?></a>
                        </h4>
                    </div>

                    <div id="<?php echo $key; ?>" class="panel-collapse collapse">
                        <div class="panel-body">
							<?PHP
							switch ($templ)
							{
								case 'previousx':
									$this->currentteam = $this->match->projectteam1_id;
									echo $this->loadTemplate($templ);
									$this->currentteam = $this->match->projectteam2_id;
									echo $this->loadTemplate($templ);
									break;
								default:
									echo $this->loadTemplate($templ);
									break;
							}
							?>
                        </div>
                    </div>
                </div>

				<?PHP
			}
			?>
        </div>

		<?PHP
	}
    elseif (version_compare(JVERSION, '1.7.0', 'ge'))
	{
		// Joomla! 1.7 code here
	}
    elseif (version_compare(JVERSION, '1.6.0', 'ge'))
	{
		// Joomla! 1.6 code here
	}
	else
	{
		// Joomla! 1.5 code here
	}
	?>
</div>
