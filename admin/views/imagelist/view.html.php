<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://www.jqueryscript.net/form/Drag-Drop-File-Upload-Dialog-with-jQuery-Bootstrap.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\String\StringHelper;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewimagelist
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewimagelist extends sportsmanagementView
{

	/**
	 * sportsmanagementViewImagehandler::init()
	 *
	 * @return
	 */
	public function init()
	{
	Factory::getLanguage()->load('com_media', JPATH_ADMINISTRATOR);   
    // Include jQuery
//		JHtml::_('jquery.framework');
//		JHtml::_('script', 'media/popup-imagemanager.js', true, true);
//		JHtml::_('stylesheet', 'media/popup-imagemanager.css', array(), true);
       $lang = Factory::getLanguage();
       $this->filter_search = '';
		
		$this->club_id = 0;
		$this->teamplayer_id = 0;
		$this->player_id = 0;

		//JHtml::_('stylesheet', 'media/popup-imagelist.css', array(), true);

		if ($lang->isRtl())
		{
		//	JHtml::_('stylesheet', 'media/popup-imagelist_rtl.css', array(), true);
		}

//		$document = JFactory::getDocument();
//		$document->addScriptDeclaration("var ImageManager = window.parent.ImageManager;");
       
   $data = Factory::getApplication()->input->getArray();
//$post   = Factory::getApplication()->input->post->getArray(array());	
//      echo '<pre>'.print_r($data,true).'</pre>';
$this->folder = $data['folder'];
$this->type = $data['type'];		
$this->fieldid = $data['fieldid'];		
$this->fieldname = $data['fieldname'];		
$this->imagelist = $data['imagelist'];

if (array_key_exists('club_id', $data)) {		
$this->club_id = $data['club_id'];
}	
if (array_key_exists('teamplayer_id', $data)) {	
$this->teamplayer_id = $data['teamplayer_id'];
}	
if (array_key_exists('player_id', $data)) {	
$this->player_id = $data['player_id'];
}
if (array_key_exists('filter_search', $data))
{
$this->filter_search = $data['filter_search'];    
}	
$this->pid = 0;
$this->match_id = 0;		
$this->mid = 0;
		
switch ($this->folder)
		{
		case "projectimages":
        $this->pid = $data['pid'];
        $this->images = $this->model->getFiles($data['folder'].'/'.$data['pid'],'',$data);
		break;  
        case "matchreport":
        $this->mid = $data['mid'];
        $this->pid = $data['pid'];
        $this->images = $this->model->getFiles($data['folder'].'/'.$data['mid'],'',$data);
		break;
	default:
		$this->images = $this->model->getFiles($data['folder'],'',$data);
		break;
          }

//$this->pid = $data['pid'];
//$this->match_id = $data['mid'];;
//$this->images = sportsmanagementModelimagelist::getFiles($data['folder'].'/'.$data['pid'],'');    
		
$this->state = $this->get('State');
//$this->items = $this->get('Items');
//$this->items = $this->images;		
$this->pagination = $this->get('Pagination');		
$this->limit = $this->state->get('list.limit'); 
//echo 'state <pre>'.print_r($this->state,true).'</pre>';      
//echo 'items<pre>'.print_r($this->getState('limitstart'),true).'</pre>';      
//echo 'pagination<pre>'.print_r($this->pagination,true).'</pre>';   		

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);
if (version_compare($baseVersion, '4.0', 'ge'))
{		
$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/media-browser.css', 'text/css');		
}		
	
/** Build the script. */
$script = array();    

if ( $this->player_id )
{
$script[] = "
function exportToForm(img) {
var baseajaxurl = '" . Uri::root() . "administrator/index.php?option=com_sportsmanagement';
var club_id = '".$this->club_id."';
var teamplayer_id = '".$this->teamplayer_id."';
var player_id = '".$this->player_id."';	
var querystring = '&player_id=' + player_id 
	+  '&picture=' + img;
	var url = baseajaxurl + '&task=imagehandler.saveimageplayer&tmpl=component';
console.log(\"url: \" + url);
console.log(\"querystring: \" + querystring);
var link = url + querystring;
console.log(\"link: \" + link);
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:'json',
  success: imagesaved //function to be called on successful reply from server
}); 

}

function imagesaved(response) 
{
// first line contains the status, second line contains the new row.
var resp = response.split('&');
if (resp[0] != '0') 
{
console.log(\"gesichert: \" + resp[0]);
}
else 
{
console.log(\"fehler: \" + resp[1]);
}
window.parent.SqueezeBox.close();
window.parent.jQuery('.modal.in').modal('hide');
}

 ";
}
elseif ( $this->club_id )
{
$script[] = "
function exportToForm(img) {
var baseajaxurl = '" . Uri::root() . "administrator/index.php?option=com_sportsmanagement';
var club_id = '".$this->club_id."';
var teamplayer_id = '".$this->teamplayer_id."';
var player_id = '".$this->player_id."';	
var querystring = '&club_id=' + club_id 
	+  '&picture=' + img;
	var url = baseajaxurl + '&task=imagehandler.saveimageclub&tmpl=component';
console.log(\"url: \" + url);
console.log(\"querystring: \" + querystring);
var link = url + querystring;
console.log(\"link: \" + link);
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:'json',
  success: imagesaved //function to be called on successful reply from server
}); 

}

function imagesaved(response) 
{
// first line contains the status, second line contains the new row.
var resp = response.split('&');
if (resp[0] != '0') 
{
console.log(\"gesichert: \" + resp[0]);
}
else 
{
console.log(\"fehler: \" + resp[1]);
}
window.parent.SqueezeBox.close();
window.parent.jQuery('.modal.in').modal('hide');
}

 ";
}
elseif ( $this->teamplayer_id )
{
$script[] = "
function exportToForm(img) {
var baseajaxurl = '" . Uri::root() . "administrator/index.php?option=com_sportsmanagement';
var club_id = '".$this->club_id."';
var teamplayer_id = '".$this->teamplayer_id."';
var player_id = '".$this->player_id."';	
var querystring = '&teamplayer_id=' + teamplayer_id 
	+  '&picture=' + img;
	var url = baseajaxurl + '&task=imagehandler.saveimageteamplayer&tmpl=component';
console.log(\"url: \" + url);
console.log(\"querystring: \" + querystring);
var link = url + querystring;
console.log(\"link: \" + link);
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:'json',
  success: imagesaved //function to be called on successful reply from server
}); 

}

function imagesaved(response) 
{
// first line contains the status, second line contains the new row.
var resp = response.split('&');
if (resp[0] != '0') 
{
console.log(\"gesichert: \" + resp[0]);
}
else 
{
console.log(\"fehler: \" + resp[1]);
}
window.parent.SqueezeBox.close();
window.parent.jQuery('.modal.in').modal('hide');
}

 ";
}



else
{
$script[] = "
function exportToForm(img) {

var club_id = '".$this->club_id."';
var teamplayer_id = '".$this->teamplayer_id."';
var player_id = '".$this->player_id."';

//     alert(img);
//     alert(\'<?php echo $this->folder; ?>\');
var logopfad;     
var type = '".$this->type."';     
var fieldid = '".$this->fieldid."';
var fieldname = '".$this->fieldname."';     
console.log(\"bild: \" + img);	
console.log(\"pfad: \" + '".$this->folder."');	
console.log(\"fieldid: \" + '".$this->fieldid."');     
console.log(\"type: \" + '".$this->type."');      
console.log(\"fieldname: \" + '".$this->fieldname."');
logopfad = 'images/com_sportsmanagement/database/".$this->folder."' + img;
console.log(\"logopfad : \" + logopfad );	
window.parent.selectImage_".$this->type."(img, img,fieldname ,fieldid);
//window.closeModal();
window.parent.jQuery('.modal.in').modal('hide');
 }
 ";
}		
		
		
/** Add the script to the document head. */
Factory::getDocument()->addScriptDeclaration(implode("\n", $script));    
    
    
    
    	
  }
  
/**
	 * Set the active image
	 *
	 * @param   integer  $index  Image position
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = &$this->images[$index];
		}
		else
		{
			$this->_tmp_img = new JObject;
		}
	}
	
  }
