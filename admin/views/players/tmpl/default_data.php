<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage players
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;

$user            = Factory::getUser();
$userId          = $user->get('id');
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
if ($this->assign)
{
	$this->readonly = ' readonly ';
}
else
{
	$this->readonly = '';
}
?>

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha512-MoRNloxbStBcD8z3M/2BmnT+rg4IsMxPkXaGh2zD6LGNNFE80W3onsAhRcMAMrSoyWL9xD7Ert0men7vR8LUZg==" crossorigin="anonymous" /> -->
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" />
<!-- <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.css" crossorigin="anonymous" />
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script> -->
  
  
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js" integrity="sha512-ubuT8Z88WxezgSqf3RLuNi5lmjstiJcyezx34yIU2gAHonIi27Na7atqzUZCOoY4CExaoFumzOsFQ2Ch+I/HCw==" crossorigin="anonymous"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha512-M5KW3ztuIICmVIhjSqXe01oV2bpe248gOxqmlcYrEzAvws7Pw3z6BK0iGbrwvdrUQUhi3eXgtxp5I8PDo9YfjQ==" crossorigin="anonymous"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.0/moment-with-locales.min.js" integrity="sha512-EATaemfsDRVs6gs1pHbvhc6+rKFGv8+w4Wnxk4LmkC0fzdVoyWb+Xtexfrszd1YuUMBEhucNuorkf8LpFBhj6w==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.31/moment-timezone-with-data-10-year-range.min.js" integrity="sha512-Rb9RCtecTEK3SdnnQhrZx4GM1ascb2CNHybgugRDTriP/b1As79OemxeIT5qs6RMJ/fCpeJrDjtpASh7I7EKMQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.js" crossorigin="anonymous"></script>


<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>

            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME', 'pl.firstname', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME', 'pl.nickname', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME', 'pl.lastname', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY', 'pl.birthday', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            
            <?php if (ComponentHelper::getParams($this->option)->get('backend_show_players_knvbnr')){ ?>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSON_REGISTRATION_NUMBER', 'pl.knvbnr', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <?php } ?>
            
            <?php if (ComponentHelper::getParams($this->option)->get('backend_show_players_agegroup')){ ?>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <?php } ?>

            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY', 'pl.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_POSITION', 'pl.position_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'pl.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'pl.id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan='6'><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan='6'>
				<?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
		<?php
	foreach ($this->items as $this->count_i => $this->item)
		{
			if (($this->item->firstname != '!Unknown') && ($this->item->lastname != '!Player')) // Ghostplayer for match-events
			{
				$link       = Route::_('index.php?option=com_sportsmanagement&task=player.edit&id=' . $this->item->id);
				$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
				$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
				$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'players.', $canCheckin);
				$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.player.' . $this->item->id) && $canCheckin;

				?>
                <tr class="row<?php echo $this->count_i % 2; ?>" >
                    <td class="center">
						<?php
						echo $this->pagination->getRowOffset($this->count_i);
						?>
                    </td>
                    <td class="center">
						<?php
						echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id);
						?>
                    </td>
					<?php
					$inputappend = $this->readonly;
					?>
                    <td class="center">
						<?php if ($this->item->checked_out) : ?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'players.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit && !$this->assign) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=player.edit&id=' . (int) $this->item->id); ?>">
								<?php echo $this->escape($this->item->firstname . ' ' . $this->item->lastname); ?></a>
						<?php elseif (!$this->assign) : ?>
							<?php echo $this->escape($this->item->firstname . ' ' . $this->item->lastname); ?>
						<?php endif; ?>

                        <input <?php echo $inputappend; ?> type="text" size="15"
                                                           class="form-control form-control-inline"
                                                           name="firstname<?php echo $this->item->id; ?>"
                                                           value="<?php echo stripslashes(htmlspecialchars($this->item->firstname)); ?>"
                                                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    </td>
                    <td class="center">
                        <input <?php echo $inputappend; ?> type="text" size="15"
                                                           class="form-control form-control-inline"
                                                           name="nickname<?php echo $this->item->id; ?>"
                                                           value="<?php echo stripslashes(htmlspecialchars($this->item->nickname)); ?>"
                                                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    </td>
                    <td class="center">
                        <input <?php echo $inputappend; ?> type="text" size="15"
                                                           class="form-control form-control-inline"
                                                           name="lastname<?php echo $this->item->id; ?>"
                                                           value="<?php echo stripslashes(htmlspecialchars($this->item->lastname)); ?>"
                                                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    </td>
                    <td class="center">
						<?php
						$picture    = ($this->item->picture == sportsmanagementHelper::getDefaultPlaceholder("player")) ? 'information.png' : 'ok.png';
						$imageTitle = ($this->item->picture == sportsmanagementHelper::getDefaultPlaceholder("player")) ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/'.$picture,$imageTitle,$image_attributes);
						$playerName = sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 0);
						echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_person' . $this->item->id, Uri::root() . $this->item->picture, $playerName, '20', Uri::root() . $this->item->picture);
						?>
                        <br />
 <?php
$link2 = 'index.php?option=com_sportsmanagement&view=imagelist' .'&player_id='.$this->item->id.
'&imagelist=1&asset=com_sportsmanagement&folder=persons' . '&author=&fieldid=' . '&tmpl=component&type=persons'.'&fieldname=picture';
echo sportsmanagementHelper::getBootstrapModalImage('select'.$this->item->id, '', Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE').' ', '20', Uri::base() . $link2, $this->modalwidth, $this->modalheight);        
        ?>                        
                    </td>
                    <td class="nowrap" class="center">
						<?php
						$append = '';
						$date1  = sportsmanagementHelper::convertDate($this->item->birthday, 1);
						if (($date1 == '00-00-0000') || ($date1 == ''))
						{
							$append = ' style="background-color:#FFCCCC;" ';
							$date1  = '';
						}
						/**
						 * das wurde beim kalender geändert
						 * $attribs = array(
						 *            'onChange' => "alert('it works')",
						 *            "showTime" => 'false',
						 *            "todayBtn" => 'true',
						 *            "weekNumbers" => 'false',
						 *            "fillTable" => 'true',
						 *            "singleHeader" => 'false',
						 *        );
						 *    echo HTMLHelper::_('calendar', Factory::getDate()->format('Y-m-d'), 'date', 'date', '%Y-%m-%d', $attribs); ?>
						 */

/*
						$attribs = array(
							'onChange' => "document.getElementById('cb" . $this->count_i . "').checked=true",
							'readonly' => trim($this->readonly),
						);
						echo HTMLHelper::calendar(
							$date1,
							'birthday' . $this->item->id,
							'birthday' . $this->item->id,
							'%d-%m-%Y',
							$attribs
						);
						*/								}
						?>

<div class="input-group date" id="birthday<?php echo $this->item->id; ?>" data-target-input="nearest"  >
                    <input type="text" 
                    name="birthday<?php echo $this->item->id; ?>"
style="width: 120px; <?php echo $append; ?>" 
                    data-toggle="datetimepicker"
                    class="form-control datetimepicker-input " data-target="#birthday<?php echo $this->item->id; ?>" value="<?php echo $date1; ?>"  />
<!--
                    <div class="input-group-append" data-target="#datepicker<?php echo $row->id; ?>" data-toggle="datetimepicker">
                       <div class="input-group-text" style="position:relative"><i class="fa fa-calendar"></i></div>
                    </div>
					 -->
</div>  


<script type="text/javascript">
            jQuery(function ($) {
                $('#birthday<?php echo $this->item->id; ?>').datetimepicker(
                {
                    format: 'DD-MM-YYYY'
                }
                );
		$("#birthday<?php echo $this->item->id; ?>").on("change.datetimepicker", ({date, oldDate}) => {
              console.log("New date", date);
              console.log("Old date", oldDate);
			  document.getElementById('cb<?php echo $this->count_i; ?>').checked=true
              //alert("Changed date")
      })    
            });
</script>                            
                        
                    </td>
                    <?php if (ComponentHelper::getParams($this->option)->get('backend_show_players_knvbnr')){ ?>
                    <td class="center">
                    <input <?php echo $inputappend; ?> type="text" size="15"
                                                           class="form-control form-control-inline"
                                                           name="knvbnr<?php echo $this->item->id; ?>"
                                                           value="<?php echo stripslashes(htmlspecialchars($this->item->knvbnr)); ?>"
                                                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    </td>
                    <?php } ?>
                    <?php if (ComponentHelper::getParams($this->option)->get('backend_show_players_agegroup')){ ?>
                    <td class="center">
						<?php
						$inputappend = $this->readonly;
						$append      = ' style="background-color:#bbffff"';
						echo HTMLHelper::_(
							'select.genericlist',
							$this->lists['agegroup'],
							'agegroup' . $this->item->id,
							$inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
							$this->count_i . '\').checked=true"' . $append,
							'value', 'text', $this->item->agegroup_id
						);
						?>
                    </td>
                    <?php } ?>

                    <td class="nowrap" class="center">
						<?php
						$append = '';
						if (empty($this->item->country))
						{
							$append = ' background-color:#FFCCCC;';
						}
						echo JHtmlSelect::genericlist(
							$this->lists['nation'],
							'country' . $this->item->id,
							$inputappend . ' class="form-control form-control-inline" style="width:140px; ' . $append . '" onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"',
							'value',
							'text',
							$this->item->country
						);
						?>
                    </td>
                    <td class="nowrap" class="center">
						<?php
						$append = '';
						if (empty($row->position_id))
						{
							$append = ' background-color:#FFCCCC;';
						}
						echo JHtmlSelect::genericlist(
							$this->lists['positions'],
							'position' . $this->item->id,
							$inputappend . 'class="form-control form-control-inline" style="width:140px; ' . $append . '" onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"',
							'value',
							'text',
							$this->item->position_id
						);
						?>
                    </td>
                    <td class="center">
                        <div class="btn-group">
							<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'players.', $canChange, 'cb'); ?>
							<?php // Create dropdown items and render the dropdown list.
							if ($canChange && !$this->assign)
							{
								HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'players');
								HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'players');
								echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->firstname . ' ' . $this->item->lastname));
							}
							?>
                        </div>


                    </td>
                    <td class="center"><?php echo $this->item->id; ?></td>
                </tr>
				<?php
				//$k = 1 - $k;
			}
		}
		?>
        </tbody>
    </table>
</div>
