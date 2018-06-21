<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage results
 */

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="container-fluid">
	<?php
    
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}    
    
    
	echo $this->loadTemplate('projectheading');

	if ( $this->config['show_sectionheader'] )
	{
		echo $this->loadTemplate('sectionheader');
	}
	
	if ( $this->config['show_matchday_pagenav'] == 2 || $this->config['show_matchday_pagenav'] == 3 )
	{
		echo $this->loadTemplate('pagnav');
	}

	echo $this->loadTemplate('results');

	if ( $this->config['show_matchday_pagenav'] == 1 || $this->config['show_matchday_pagenav'] == 3 )
	{
		echo $this->loadTemplate('pagnav');
	}
    
    if ( $this->overallconfig['show_project_rss_feed'] )
	{
		if ( $this->rssfeeditems )
        {
        echo $this->loadTemplate('rssfeed');    
        }
	}
/*
if ( $this->config['show_summary'] )
{
	echo $this->loadTemplate('summary');	
}
*/    
?>
<div>
<?PHP
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
</div>
