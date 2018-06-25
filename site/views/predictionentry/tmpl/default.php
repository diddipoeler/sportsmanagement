<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die('Restricted access');

//echo 'project_id<pre>'.print_r($this->model->predictionProject->project_id, true).'</pre><br>';

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews','predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="row-fluid">
<?php

	echo $this->loadTemplate('predictionheading');
	echo $this->loadTemplate('sectionheader');

	if ( ( !isset($this->actJoomlaUser) ) || ( $this->actJoomlaUser->id == 0 ) )
	{
		echo $this->loadTemplate('view_deny');
	}
	else
	{
		if ( ( !$this->isPredictionMember ) && ( !$this->allowedAdmin ) )
		{
			echo $this->loadTemplate('view_not_member');
		}
		else
		{
			if ($this->isNewMember)
            {
                echo $this->loadTemplate('view_welcome');
                }

			if (!$this->tippEntryDone)
			{
				if (($this->config['show_help']==0)||($this->config['show_help']==2))
                {
                    echo $this->model->createHelptText($predictionProject->mode);
                }
                echo $this->loadTemplate('view_tippentry_do');
                //echo $this->loadTemplate('matchday_nav');
            if (($this->config['show_help']==1)||($this->config['show_help']==2))
			{
				echo $this->model->createHelptText($predictionProject->mode);
			}
            
			}
			else
			{
				echo $this->loadTemplate('view_tippentry_done');
			}
		}
	}
	
?>
<div>
<?PHP
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
<?PHP

?>

</div>