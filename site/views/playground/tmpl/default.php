<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//echo ' playground<br><pre>'.print_r($this->playground,true).'</pre>';
//echo ' temas<br><pre>'.print_r($this->teams,true).'</pre>';
//echo ' config<br><pre>'.print_r($this->config,true).'</pre>';
//echo ' project<br><pre>'.print_r($this->project,true).'</pre>';

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JURI::root().'tmp'.DS.$this->playground->id.'-playground.kml';
$this->kmlfile = $this->playground->id.'-playground.kml';

?>

<div class="container-fluid" id="playground">
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
		
	if ( $this->config['show_playground'] )
	{ 
		echo $this->loadTemplate('playground');
	}
		
	if ( $this->config['show_extended'] )
	{
		echo $this->loadTemplate('extended');
	}
		
	if ( $this->config['show_picture'] )
	{ 
		echo $this->loadTemplate('picture');
	}
		
	if ( $this->config['show_maps'] )
	{ 
		echo $this->loadTemplate('googlemap');
	}
	
    if ( $this->config['show_route'] )
	{ 
		echo $this->loadTemplate('googlemap_route');
	}
    	
	if ( $this->config['show_description'] )
	{ 
		echo $this->loadTemplate('description');
	}

	if ( $this->config['show_teams'] )
	{ 
		echo $this->loadTemplate('teams');
	}

	if ( $this->config['show_matches'] )
	{ 
		echo $this->loadTemplate('matches');
	}	

	if ( $this->config['show_played_matches'] )
	{ 
		echo $this->loadTemplate('played_matches');
	}
	
	?>
<div id="backbuttonfooter">
<?PHP    
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
    
</div>
