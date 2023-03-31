<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://www.tutorialrepublic.com/codelab.php?topic=bootstrap&file=accordion
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

if ($params->get("show_slider"))
{
	$ausland = array();
	$auslandfed = array();
	$zaehler = 0;
	foreach ($list as $row)
	{
		$ausland[$row->country] = JSMCountries::getCountryName($row->country);
		$auslandfed[$row->country] = $row->federation;
		$zaehler++;
	}

	$zaehler = 0;
	$zaehlerfed = 0;
	asort($ausland);	


//echo '<pre>'.print_r($ausland,true).'</pre>';	
//echo '<pre>'.print_r($list,true).'</pre>';	
//echo '<pre>'.print_r($auslandfed,true).'</pre>';
//echo '<pre>'.print_r($countryfederation,true).'</pre>';
	
echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabsfederation', array('active' => 'show_table_0')); // Start tab set
foreach ($federation as $keyfed => $valuefed) if ( $keyfed != 0 )
{
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsfederation', 'show_table_'.$zaehler, Text::_($valuefed->name));
foreach ($countryfederation as $key => $value) if ( $value->federation == $keyfed  )
{
//echo $value->alpha3;
foreach ($list as $row)
						{
							if ($row->country == $value->alpha3)
							{
								$createroute = array("option"             => "com_sportsmanagement",
								                     "view"               => "ranking",
								                     "cfg_which_database" => 0,
								                     "s"                  => 0,
								                     "p"                  => $row->project_slug,
								                     "type"               => 0,
								                     "r"                  => $row->roundcode,
								                     "from"               => 0,
								                     "to"                 => 0,
								                     "division"           => 0,
										    "Itemid"           => -1,);

								$query = sportsmanagementHelperRoute::buildQuery($createroute);
								$link  = Route::_('index.php?' . $query, false);
								?>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <a href="<?PHP echo $link; ?>"
                                       class="<?PHP echo $params->get('button_class'); ?>  btn-block" role="button">
<span>
<?PHP
echo JSMCountries::getCountryFlag($row->country);
?>
</span>
										<?PHP
										echo Text::_($row->name);
										?>
                                    </a>
                                    <!-- </button> -->
                                </div>
								<?php
							}
						}



}							
echo HTMLHelper::_('bootstrap.endTab');
$zaehler++;
}
?>




<?php
}
else
{
	$start = 1;

	foreach ($list as $row)
	{
		if ($start == 1)
		{
			?>
            <div class="row-fluid">
			<?PHP
		}

		$createroute = array("option"             => "com_sportsmanagement",
		                     "view"               => "ranking",
		                     "cfg_which_database" => 0,
		                     "s"                  => 0,
		                     "p"                  => $row->project_slug,
		                     "type"               => 0,
		                     "r"                  => $row->roundcode,
		                     "from"               => 0,
		                     "to"                 => 0,
		                     "division"           => 0,);

		$query = sportsmanagementHelperRoute::buildQuery($createroute);
		$link  = Route::_('index.php?' . $query, false);
		?>
        <div class="col-sm-2">
            <a href="<?PHP echo $link; ?>" class="<?PHP echo $params->get('button_class'); ?>  btn-block" role="button">
		<span>
		<?PHP
		echo JSMCountries::getCountryFlag($row->country);
		?>
		</span>
				<?PHP
				echo Text::_($row->name);
				?>
            </a>
            <!-- </button> -->
        </div>
		<?PHP
		$start++;

		if ($start == 7)
		{
			$start = 1;
			?>
            </div>
			<?PHP
		}
	}
}
