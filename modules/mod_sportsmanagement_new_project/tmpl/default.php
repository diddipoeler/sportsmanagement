<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_new_project
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.html.pane');


echo HTMLHelper::_('sliders.start', 'neueligen', array('useCookie' => 1));
echo HTMLHelper::_('sliders.panel', Text::_('Neue Ligen'), 'neueligen');
?>


    <table width="100%" class="">

		<?PHP

		$zeile = 0;
		echo '<tr>';

		if (sizeof($list) == 0)
		{
			echo '<td width="" >';
			echo '<b>Heute gibt es leider keine neuen Ligen!</b>';
			echo '</td>';
		}
		else
		{
			echo '<td colspan="2" width="" >';
			echo '<b>Wir haben ' . sizeof($list) . ' neue/aktualisierte Ligen !</b>';
			echo '</td>';

			foreach ($list as $row)
			{
				if ($zeile < 10)
				{
					if ($zeile % 2 == 0)
					{
						// Zahl ist gerade
						echo '</tr><tr>';
					}
					else
					{
						// Zahl ist ungerade
						echo '</tr><tr>';
					}

					echo '<td width="" >';
					echo JSMCountries::getCountryFlag($row->country);
					echo '</td>';

					echo '<td>';
/*
					$createroute = array("option"             => "com_sportsmanagement",
					                     "view"               => "resultsranking",
					                     "cfg_which_database" => 0,
					                     "s"                  => 0,
					                     "p"                  => $row->id,
					                     "r"                  => $row->roundcode);

					$query = sportsmanagementHelperRoute::buildQuery($createroute);
					$link  = Route::_('index.php?' . $query, false);
                    */
$routeparameter                       = array();
				$routeparameter['cfg_which_database'] = $app->input->getInt('cfg_which_database', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0));
				$routeparameter['s']                  = $app->input->getInt('s', 0);
				$routeparameter['p']                  = $row->id;
				$routeparameter['r']                  = $row->roundcode;
				$routeparameter['division']           = 0;
				$routeparameter['mode']               = 0;
				$routeparameter['order']              = 0;
				$routeparameter['layout']             = 0;
				$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute("resultsranking", $routeparameter);                    
                    
                    
					echo HTMLHelper::link($link, Text::_($row->name . ' - ( ' . $row->liganame . ' )'));
					echo '</td>';
					$zeile++;
				}
			}
		}

		echo '</tr>';


		?>

    </table>

    <div style="float: left;">
        <table width="100%" class="">
			<?PHP

			if (sizeof($list))
			{
				echo '<tr>';

				$lfdnummer = 0;

				foreach ($list as $row)
				{
					if ($zeile > 9)
					{
						if ($lfdnummer > 9)
						{
							if ($zeile % 2 == 0)
							{
								// Zahl ist gerade
								echo '</tr><tr>';
							}

							/*
							else
							{
							 // Zahl ist ungerade
							echo '<tr>';
							}
							*/

							echo '<td width="" >';
							echo JSMCountries::getCountryFlag($row->country);
							echo '</td>';
							echo '<td>';
							$createroute = array("option" => "com_sportsmanagement",
							                     "view"   => "resultsranking",
							                     "p"      => $row->id,
							                     "r"      => $row->roundcode);
							$query       = sportsmanagementHelperRoute::buildQuery($createroute);
							$link        = Route::_('index.php?' . $query, false);
							echo HTMLHelper::link($link, Text::_($row->name . ' - ( ' . $row->liganame . ' )'));
							echo '</td>';
						}

						$zeile++;
					}

					$lfdnummer++;
				}

				echo '</tr>';
			}
			?>
        </table>
    </div>
<?php
echo HTMLHelper::_('sliders.end');

