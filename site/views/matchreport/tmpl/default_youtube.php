<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_youtube.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 *
 * https://www.html-seminar.de/responsive-webdesign-externe-videos-einbinden.htm
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;


function getYoutubeEmbedHtml($url)
{ 
    $query = parse_url($url, PHP_URL_QUERY); 
    $args = array(); 
    parse_str($query, $args); 
    $videoId = isset($args["v"]) ? $args["v"] : null; 
    if(!$videoId) { 
        return null; 
    } 
    return "<style>.iframe-container { position:relative; margin-bottom: 30px; padding-bottom:56.25%; padding-top:25px; height:0; max-width:100%; } .iframe-container iframe { position:absolute; top:0; left:0; width:100%; height:100%; border:none; }</style><div class=\"iframe-container\"><iframe src=\"https://www.youtube-nocookie.com/embed/{$videoId}\" allowfullscreen=\"\"></iframe><br /></div>"; 
} 

?>
 
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_YOUTUBE'); ?></h4>
<div class="panel-group" id="showyoutube">    

<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#showyoutube" href="#countall"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_YOUTUBE_SHOW'); ?></a>
</h4>
</div>  
<div id="countall" class="panel-collapse collapse">
<div class="panel-body">
<div id="videobereich">	
<iframe class="videoextern" width="640" height="360"
 src="<?php echo $this->youtube;?>"
 frameborder="0" allowfullscreen></iframe>
</div>

  
<?php
echo $this->youtube;  
echo getYoutubeEmbedHtml($this->youtube);   
?>  
</div>
</div>
</div>
