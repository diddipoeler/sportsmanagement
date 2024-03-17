<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_subst.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

usort(
	$this->substitutes, function ($a, $b) {
	return $a->in_out_time > $b->in_out_time;
}
);
?>
<!-- START of Substitutions -->
<div class="<?php echo $this->divclassrow; ?> " id="matchreport-subst">
	<?php
	if ($this->config['show_substitutions'])
	{
		if (!empty($this->substitutes))
		{
			?>
            <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'); ?></h2>
            <div class="row ">
               
                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-end" style="">
                      
                        <ul class="">
                          <?php
							foreach ($this->substitutes as $sub)
							{
								if ($sub->ptid == $this->match->projectteam1_id)
								{
									?>
                                    <li class="list"><?php echo $this->showSubstitution($sub); ?></li><?php
								}
							}
							?>
                      </ul>
                      
                    </div>
                  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2" style="">
                  </div>
                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
                        <ul class="">
                          <?php
							foreach ($this->substitutes as $sub)
							{
								if ($sub->ptid == $this->match->projectteam2_id)
								{
									?>
                                    <li class="list"><?php echo $this->showSubstitution($sub); ?></li><?php
								}
							}
							?>
                      </ul>
                    </div>
             
            </div>
			<?php
		}
	}
	?>
</div>
<!-- END of Substitutions -->
