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


<div class="accordion_one" id="accordionactseasonfederation">
<?php
foreach ($federation as $keyfed => $valuefed) if ( $keyfed != 0 )
		{
if (empty($zaehlerfed))
			{
				$collapsefed = 'show';
				$zaehlerfed++;
			}
			else
			{
				$collapsefed = '';
			}

?>
<div class="accordion-item">
    <h2 class="accordion-header" id="headingctseasonfed<?php echo $keyfed; ?>">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsectseasonfed<?php echo $keyfed; ?>" aria-expanded="true" aria-controls="collapsectseasonfed<?php echo $keyfed; ?>">
        <?php echo $valuefed->name; ?>
      </button>
    </h2>
    <div id="collapsectseasonfed<?php echo $keyfed; ?>" class="accordion-collapse collapse <?php echo $collapsefed; ?>" aria-labelledby="headingctseasonfed<?php echo $keyfed; ?>" data-bs-parent="#accordionactseasonfederation">
      <div class="row">


<div class="accordion_two" id="accordionactseason">
<?php
/** anfang schleife länder */
		foreach ($auslandfed as $key => $value) if ( $value == $keyfed  )
		{
			if (empty($zaehler))
			{
				$collapse = 'show';
				$zaehler++;
			}
			else
			{
				$collapse = '';
			}
?>
<div class="accordion-item">
    <h2 class="accordion-header" id="headingctseason<?php echo $key; ?>">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsectseason<?php echo $key; ?>" aria-expanded="true" aria-controls="collapsectseason<?php echo $value; ?>">
        <?php echo JSMCountries::getCountryFlag($key) . ' ' . $value; ?>
      </button>
    </h2>
    <div id="collapsectseason<?php echo $key; ?>" class="accordion-collapse collapse <?php echo $collapse; ?>" aria-labelledby="headingctseason<?php echo $key; ?>" data-bs-parent="#accordionactseason">
      <div class="row">
<?php
						foreach ($list as $row)
						{
							if ($row->country == $key)
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
                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-4">
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
						?>        
      </div>
    </div>
  </div>
<!-- end div season item -->
<?php
}
/** ende schleife länder */
?>
</div>
<!-- end div season -->

</div>
<!-- ende div row fed -->

<?php
}
?>
</div>
<!-- ende div collaps fed -->
</div>
<!-- ende div item fed -->
</div>
<!-- ende div federation -->

<?php	
	
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
