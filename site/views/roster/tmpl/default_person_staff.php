<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_person_staff.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<div class="jl_rosterperson jl_rp<?php echo $this->k;?>">
<?php
$personName = sportsmanagementHelper::formatName(null, $this->row->firstname, $this->row->nickname, $this->row->lastname, $this->config["name_format_staff"]);

if ($this->config['show_staff_icon'] == 1)
{
	$imgTitle = Text::sprintf($personName);
	$picture = $this->row->picture;

	if ((empty($picture)) || ($picture == 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png' ))
	{
		$picture = $this->row->ppic;
	}


	if (!file_exists($picture))
	{
		$picture = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
	}

?>
			<div class="jl_rosterperson_staffpicture_column">
				<div class="jl_roster_staffperson_pic">
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage(
	'st' . $this->row->person_id,
	$picture,
	$personName,
	$this->config['staff_picture_width'],
	'',
	$this->modalwidth,
	$this->modalheight,
	$this->overallconfig['use_jquery_modal']
);

?>
				</div><!-- /.jl_roster_staffperson_pic -->
			</div><!-- /.jl_rosterperson_staffpicture_column -->
<?php
}//if ($this->config['show_staff_icon']) ends
?>
		<div class="jl_roster_staffperson_detail_column">
			<h3>
				<span class="jl_rosterperson_name">
				<?php
				$routeparameter = array();
				$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
				$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
				$routeparameter['p'] = $this->project->slug;
				$routeparameter['tid'] = $this->team->slug;
				$routeparameter['pid'] = $this->row->person_slug;

													  echo ($this->config['link_staff'] == 1) ?
				HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter), $personName)
				: $personName;
?>
					<br />&nbsp;
				</span>
			</h3>
			<div class="jl_roster_persondetails">
					<div>
						<span class="jl_roster_persondetails_label">
<?php
							echo Text::_('COM_SPORTSMANAGEMENT_ROSTER_STAFF_FUNCTION');
?>
						</span><!-- /.jl_roster_persondetails_label -->
						<span class="jl_roster_persondetails_data">
<?php
//						if (!empty($this->row->parentname))
//						{
//						echo Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_MEMBER_OF',Text::_($this->row->parentname));
//						}
//						echo '&nbsp;' . Text::_( $this->row->position );
						$staff_position = '';

switch ($this->config['staff_position_format'])
{
	case 2:     // Show member with text
		$staff_position = Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_MEMBER_OF', Text::_($this->row->parentname));
	break;

	case 3:     // Show function with text
		$staff_position .= Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_FUNCTION_IS', Text::_($this->row->position));
	break;

	case 4:     // Show only function
		$staff_position = Text::_($this->row->parentname);
	break;

	case 5:     // Show only position
		$staff_position = Text::_($this->row->position);
	break;

	default: // Show member+function with text
		$staff_position = Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_MEMBER_OF', Text::_($this->row->parentname));
		$staff_position .= '<br />';
		$staff_position .= Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_FUNCTION_IS', Text::_($this->row->position));
	break;
}

				echo $staff_position;

								?>
						</span><!-- /.jl_roster_persondetails_data -->
					</div>
<?php
if ($this->config['show_birthday_staff'] > 0 && $this->row->birthday != "0000-00-00")
{
	switch ($this->config['show_birthday_staff'])
	{
		case 1:     // Show Birthday and Age
			$showbirthday = 1;
			$showage = 1;
			$birthdayformat = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE');
		break;

		case 2:     // Show Only Birthday
			$showbirthday = 1;
			$showage = 0;
			$birthdayformat = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE');
		break;

		case 3:     // Show Only Age
			$showbirthday = 0;
			$showage = 1;
		break;

		case 4:     // Show Only Year of birth
			$showbirthday = 1;
			$showage = 0;
			$birthdayformat = Text::_('%Y');
		break;
		default:
			$showbirthday = 0;
			$showage = 0;
		break;
	}


	if ($showage == 1)
	{
		?>
					<div>
						<span class="jl_roster_persondetails_label">
		<?php echo Text::_("COM_SPORTSMANAGEMENT_PERSON_AGE");?>
						</span>
						<span class="jl_roster_persondetails_data">
		<?php echo sportsmanagementHelper::getAge($this->row->birthday, $this->row->deathday);?>
						</span>
					</div>
	<?php
	}


	if ($showbirthday == 1)
	{
		?>
					<div>
						<span class="jl_roster_persondetails_label">
		<?php echo Text::_("COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY");?>
						</span>
						<span class="jl_roster_persondetails_data">
		<?php echo HTMLHelper::date($this->row->birthday, $birthdayformat);?>
						</span>
					</div>
	<?php
	}

	// Deathday
	if ($this->row->deathday != "0000-00-00")
	{
	?>
					<div>
						<span class="jl_roster_persondetails_label">
		<?php echo Text::_("COM_SPORTSMANAGEMENT_PERSON_DEATHDAY");?>[ &dagger; ]
						</span>
						<span class="jl_roster_persondetails_data">
		<?php echo HTMLHelper::date($this->row->deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));?>
						</span>
					</div>
	<?php
	}
}// If ($this->config['show_birthday_staff'] > 0 AND $this->row->birthday !="0000-00-00") ends

if ($this->config['show_country_flag_staff'])
{
?>
   <div>
	<span class="jl_roster_persondetails_label">
		<?php echo Text::_("COM_SPORTSMANAGEMENT_PERSON_NATIONALITY");?>
	</span><!-- /.jl_roster_persondetails_label -->
	<span class="jl_roster_persondetails_data">
<?php
 echo JSMCountries::getCountryFlag($this->row->country);
?>
	</span><!-- /.jl_roster_persondetails_data -->
   </div>
<?php
}// If ($this->config['show_country_flag']) ends
?>
				</div><!-- /.jl_roster_persondetails -->
			</div><!-- /.jl_roster_staffperson_detail_column -->
		</div><!-- /.jl_rp<?php echo $this->k;?> -->
