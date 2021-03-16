<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       imageselect.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://css-tricks.com/examples/DragAndDropFileUploading/
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

/**
 * ImageSelectSM
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
abstract class ImageSelectSM
{
	static $_foldertype = '';
	static $_view = '';

	/**
	 * ImageSelectSM::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$type              = Factory::getApplication()->input->getVar('type');
		self::$_foldertype = $type;
		self::$_view       = Factory::getApplication()->input->getVar('view');

	}

	/**
	 * ImageSelectSM::getSelector()
	 *
	 * @param   mixed   $fieldname
	 * @param   mixed   $fieldpreview_name
	 * @param   mixed   $type
	 * @param   mixed   $value
	 * @param   string  $default
	 * @param   string  $control_name
	 * @param   mixed   $fieldid
	 *
	 * @return
	 */
	public static function getSelector($fieldname, $fieldpreview_name, $type, $value, $default = '', $control_name = '', $fieldid)
	{
		$document          = Factory::getDocument();
		$app               = Factory::getApplication();
        $modalheight = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('modal_popup_height', 600);
		$modalwidth  = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('modal_popup_width', 900);
        
		if ($app->isClient('site'))
		{
		$link = 'administrator/';
		$link2 = 'administrator/';
        $use_jquery_modal = 0;
        $modalheight = 500;
		}
		else
		{
		$link = '';
		$link2 = '';
        $use_jquery_modal = 0;
		}
		
		self::$_foldertype = $type;

		

		$baseFolder = Uri::root();// .'images/com_sportsmanagement/database/'.ImageSelect::getfolder($type);
		$funcname   = preg_replace("/^[.]*/", '', $fieldid);

$typefolder = $type;

//		switch (self::$_view)
//		{
//			case 'projectteam':
//				$typefolder = $type;
//				break;
//			default:
//				$typefolder = $type;
//				break;
//		}

		/**
		 *
		 * Build the image select functionality
		 */
		$js = "
		function selectImage_" . $type . "(image, imagename, field, fieldid)
		{
		console.log('selectImage image : ' + image);
		console.log('selectImage imagename : ' + imagename);
		
			console.log('selectImage fieldid : ' + fieldid);
			console.log('selectImage field : ' + field);
			document.getElementById('copy_' + fieldid).value = 'images/com_sportsmanagement/database/" . self::getfolder($typefolder) . "/'+image;
			document.getElementById('a_' + field).value = 'images/com_sportsmanagement/database/" . self::getfolder($typefolder) . "/'+image;
			document.getElementById(fieldid).value ='images/com_sportsmanagement/database/" . self::getfolder($typefolder) . "/'+imagename;
			document.getElementById('" . $fieldid . "_preview').src = '" . Uri::root() . "images/com_sportsmanagement/database/" . self::getfolder($type) . "/'+image;
var els=document.getElementsByName(field);
for (var i=0;i<els.length;i++) {
els[i].value = 'images/com_sportsmanagement/database/" . self::getfolder($typefolder) . "/'+imagename;}

		}
      
		function reset_" . $funcname . "()
		{
        var imgSource = document.getElementById('original_" . $fieldid . "').value;
        document.getElementById('" . $fieldid . "_preview').src = '" . Uri::root() . "' + imgSource  ;
        document.getElementById('" . $fieldid . "').value = imgSource;
        document.getElementById('copy_" . $fieldid . "').value = imgSource;
      
			//var imgSource = document.getElementById('" . $fieldid . "_preview').src ;
			console.log('1.reset original : ' + imgSource );
			//document.getElementById('" . $fieldid . "').value = '" . $default . "';
			//document.getElementById('" . $fieldid . "_preview').src = '" . Uri::root() . $default . "';
			
			//var imgSource = document.getElementById('" . $fieldid . "_preview').src ;
			//console.log('2.reset fieldpreview_name : ' + imgSource );
			//console.log('3.reset default : ' + '" . $default . "' );
		}

		function clear_" . $funcname . "()
		{
var radios = document.getElementsByName('jform[gender]');
picture = '" . sportsmanagementHelper::getDefaultPlaceholder($type) . "';
pictureprev = '" . Uri::root() . sportsmanagementHelper::getDefaultPlaceholder($type) . "';
for (var i = 0, length = radios.length; i < length; i++)
{
 if (radios[i].checked)
 {
  // do whatever you want with the checked radio
  //alert(radios[i].value);
  gender = radios[i].value;
console.log('0.clear gender : ' + gender );
  // only one radio can be logically checked, don't check the rest
  break;
 }
}

if (typeof gender != 'undefined' )
{
switch(gender) {
  case '1':
picture = '" . sportsmanagementHelper::getDefaultPlaceholder('mensmall') . "';
pictureprev = '" . Uri::root() . sportsmanagementHelper::getDefaultPlaceholder('mensmall') . "';
      break;
  case '2':
picture = '" . sportsmanagementHelper::getDefaultPlaceholder('womansmall') . "';
pictureprev = '" . Uri::root() . sportsmanagementHelper::getDefaultPlaceholder('womansmall') . "';
      break;

  }
}

console.log('picture standard : ' + picture );
console.log('pictureprev : ' + pictureprev );
console.log('1.clear type: ' + '" . $type . "' );
console.log('2.clear fieldname: ' + '" . $fieldname . "' );
console.log('3.clear fieldpreview_name : ' + '" . $fieldpreview_name . "' );
//alert(select);

document.getElementById('" . $fieldid . "').value = picture;
document.getElementById('copy_" . $fieldid . "').value = picture;
document.getElementById('" . $fieldid . "_preview').src = pictureprev;
		}


			
			
		//window.addEvent('domready', function()
        jQuery(document).ready(function(){
		console.log('fieldid: " . $fieldid . "');	
		select = document.getElementById('" . $fieldid . "').value;
		console.log('select : ' + select  );
		document.getElementById('" . $fieldid . "_preview').src = '" . Uri::root() . "' + select  ;

		console.log('document.ready ready: ' + jQuery('#a_" . $fieldname . "_name').val() );	
		console.log('document.ready fieldname: ' + '" . $fieldname . "' );
		console.log('document.ready fieldid: ' + '" . $fieldid . "' );
		console.log('document.ready type: ' + '" . $type . "' );
		console.log('document.ready preview name: ' + '" . $fieldpreview_name . "' );
		
		});
		";

		$js .= '    
// Compatibility with mootools modal layout
function jInsertFieldValue(value, id) {
console.log("jInsertFieldValue value : " + value);
console.log("jInsertFieldValue id : " + id);	
jQuery("#" + id).val(value);
jQuery("#copy_" + id).val(value);
select = jQuery("#" + id).val();
console.log("jInsertFieldValue select : " + select);
var $img = jQuery("#" + id + "_preview");
console.log("jInsertFieldValue img : " + $img);	
$img.attr("src", "' . Uri::root() . '" + select);
}    
';

		$imageselect = '';

		if (ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('cfg_draganddrop'))
		{
			$layoutdrag = 'uploaddraganddrop';
		}
		else
		{
			$layoutdrag = 'upload';
		}

		$link .= 'index.php?option=com_sportsmanagement&amp;view=imagehandler&amp;layout=' . $layoutdrag . '&amp;type=' .
			$type . '&amp;field=' . $fieldname . '&amp;fieldid=' . $fieldid . '&amp;tmpl=component&pid=0&mid=0&imagelist=';

		//        $link2 = 'index.php?option=com_sportsmanagement&amp;view=imagehandler&amp;type=' .
		//		$type . '&amp;field=' . $fieldname . '&amp;fieldid=' . $fieldid .'&amp;tmpl=component';
/*
		$link2 = 'index.php?option=com_media&amp;view=images' .
			'&amp;asset=com_sportsmanagement&amp;folder=com_sportsmanagement/database/' . self::getfolder($typefolder) . '&author=&amp;fieldid=' . $fieldid . '&amp;tmpl=component';
*/
$link2 .= 'index.php?option=com_sportsmanagement&view=imagelist' .
			'&imagelist=1&asset=com_sportsmanagement&folder=' . self::getfolder($typefolder) . '&author=&fieldid=' . $fieldid . '&tmpl=component&type='.$type.'&fieldname=' . $fieldname;

		$document->addScriptDeclaration($js);

		//		HTMLHelper::_( 'behavior.modal', 'a.modal' );

		$imageselect .= "\n&nbsp;<table><tr><td><input style=\"background: #ffffff;\" type=\"text\" id=\"" . $fieldid . "\" name=\"" . $fieldname . "\"  value=\"" .
			$value . "\" disabled=\"disabled\" size=\"100\" /></td></tr>";
		$imageselect .= "<tr><td><div class=\"button2-left\"><div class=\"blank\">";
		
if ($app->isClient('site'))
{
$imageselect .= sportsmanagementHelperHtml::getBootstrapModalImage('upload' . $funcname, 'images/com_sportsmanagement/database/jl_images/up.png', Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'), '20', Uri::base() . $link, $modalwidth, $modalheight,$use_jquery_modal);		
}
else
{
$imageselect .= sportsmanagementHelper::getBootstrapModalImage('upload' . $funcname, '', Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'), '20', Uri::base() . $link, $modalwidth, $modalheight);	
}
		$imageselect .= "</div></div>\n";

		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\">";
		
if ($app->isClient('site'))
{
$imageselect .= sportsmanagementHelperHtml::getBootstrapModalImage('select' . $funcname, 'images/com_sportsmanagement/database/jl_images/zoom.png', Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE').' '.Factory::getApplication()->getUserState("com_sportsmanagement.itemname", ''), '20', Uri::base() . $link2, $modalwidth, $modalheight,$use_jquery_modal);	
}
else
{		
$imageselect .= sportsmanagementHelper::getBootstrapModalImage('select' . $funcname, '', Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE').' '.Factory::getApplication()->getUserState("com_sportsmanagement.itemname", ''), '20', Uri::base() . $link2, $modalwidth, $modalheight);
}
		$imageselect .= "</div></div>\n";

		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"btn btn-primary\" title=\"" .
			Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE') . "\" href=\"#\" onclick=\"reset_" . $fieldid . "();\">" . Text::_('JSEARCH_RESET') . "</a></div></div>\n";

		$imageselect .= "<div class=\"button2-left\"><div class=\"blank\"><a class=\"btn btn-primary\" title=\"" .
			Text::_('JCLEAR') . "\" href=\"#\" onclick=\"clear_" . $fieldid . "();\">" . Text::_('JCLEAR') . "</a></div></div>";

		$imageselect .= "</td></tr>\n";
		$imageselect .= "\n<tr><td><input type=\"hidden\" id=\"a_" . $fieldname . "\" name=\"" . $fieldname . "\" value=\"" . $value . "\" /></td></tr>";
        if ( $funcname == 'events' )
        {
        $imageselect .= "\n<tr><td><input type=\"hidden\" id=\"copy_" . $fieldid . "\" name=\"" . $fieldname . "\" value=\"" . $value . "\" /></td></tr>";
        }
        $imageselect .= "\n</table>";

		return $imageselect;
	}

	/**
	 * ImageSelectSM::getfolder()
	 *
	 * @param   mixed  $type
	 *
	 * @return
	 */
	static function getfolder($type)
	{
		
?>
<script>
console.log('getfolder: ' + '<?php echo $type;  ?>' );  
</script>  
<?php		
		switch ($type)
		{
			case "clubs_small":
			case "clubssmall":
			case "clubs/small":
				return "clubs/small";
				break;
			case "clubs_medium":
			case "clubsmedium":
			case "clubs/medium":
				return "clubs/medium";
				break;
			case "clubs_large":
			case "clubslarge":
			case "clubs/large":
				return "clubs/large";
				break;
			case "clubs_trikot_home":
				return "clubs/trikot";
				break;
			case "clubs_trikot_away":
				return "clubs/trikot";
				break;
			case "flags":
				return "flags";
				break;
			case "flags_associations":
				return "flags_associations";
				break;
			case "associations":
				return "associations";
				break;
			case "events":
				return "events";
				break;
			case "leagues":
				return "leagues";
				break;
			case "divisions":
				return "divisions";
				break;
			case "persons":
				return "persons";
				break;
			case "projectreferee":
				//return "persons";
                return "projectreferees";
				break;
			case "playgrounds":
				return "playgrounds";
				break;
			case "positions":
				return "positions";
				break;
			case "projects":
				return "projects";
				break;
			case "projectteams":
				return "projectteams";
				break;
			case "projectteams_trikot_home":
				return "projectteams/trikot_home";
				break;
			case "projectteams_trikot_away":
				return "projectteams/trikot_away";
				break;
			case "seasons":
				return "seasons";
				break;
			case "sport_types":
				return "sport_types";
				break;
			case "statistics":
				return "statistics";
				break;
			case "teamplayers":
				return "teamplayers";
				break;
			case "teams":
				return "teams";
				break;
			case "teamstaffs":
				return "teamstaffs";
				break;
			case "venues":
				return "venues";
				break;
			case "rounds":
				return "rounds";
				break;
                case "rosterground":
				return "rosterground";
				break;
			case "agegroups":
				return "agegroups";
				break;
            case "projectimages":
                //$data = Factory::getApplication()->input->getArray();
				//return "projectimages/".$data['pid'];
                return "projectimages";
				break;
				case "matchreport":
                //$data = Factory::getApplication()->input->getArray();
				//return "projectimages/".$data['pid'];
                return "matchreport";
				break;
			default:
				return "events/" . $type;
		}
	}

	/**
	 * ImageSelectSM::check()
	 *
	 * @param   mixed  $file
	 *
	 * @return
	 */
	public static function check($file)
	{
		jimport('joomla.filesystem.file');
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_sportsmanagement');

		$sizelimit = $params->get('image_max_size', 120) * 1024; // Size limit in kb
		$imagesize = $file['size'];
		/**
		 *
		 * check if the imagefiletype is valid
		 */
		$fileext = File::getExt($file['name']);

		$allowable = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'svg', 'GIF', 'JPG', 'JPEG', 'PNG', 'BMP', 'SVG');

		if (!in_array($fileext, $allowable))
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR1') . ' ' . htmlspecialchars($$file['name'], ENT_COMPAT, 'UTF-8'), Log::WARNING, 'jsmerror');

			return false;
		}

		/**
		 *
		 * Check filesize
		 */
		if ($imagesize > $sizelimit)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR2') . ' ' . htmlspecialchars($$file['name'], ENT_COMPAT, 'UTF-8'), Log::WARNING, 'jsmerror');

			return false;
		}

		/**
		 *
		 * XSS check
		 */
		$xss_check = File::read($file['tmp_name'], false, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big',
			'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col',
			'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn',
			'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe',
			'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
			'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript',
			'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp',
			'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style',
			'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var',
			'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');

		foreach ($html_tags as $tag)
		{
			/**
			 *
			 * A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			 */
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_IE_WARN'), Log::WARNING, 'jsmerror');

				return false;
			}
		}

		return true;
	}

	/**
	 * Sanitize the image file name and return an unique string
	 *
	 * @param   string  $base_Dir  the target directory
	 * @param   string  $filename  the unsanitized imagefile name
	 *
	 * @return string $filename the sanitized and unique image file name
	 * @author Christoph Lukes
	 *
	 * @since  0.9
	 */
	public static function sanitize($base_Dir, $filename)
	{
		jimport('joomla.filesystem.file');

		/** check for any leading/trailing dots and remove them (trailing shouldn't be possible cause of the getEXT check) */
		$filename = preg_replace("/^[.]*/", '', $filename);
		$filename = preg_replace("/[.]*$/", '', $filename); // Shouldn't be necessary, see above

		/** we need to save the last dot position cause preg_replace will also replace dots */
		$lastdotpos = strrpos($filename, '.');

		/** replace invalid characters */
		$chars    = '[^0-9a-zA-Z()_-]';
		$filename = strtolower(preg_replace("/$chars/", '_', $filename));

		/** get the parts before and after the dot (assuming we have an extension...check was done before) */
		$beforedot = substr($filename, 0, $lastdotpos);
		$afterdot  = substr($filename, $lastdotpos + 1);

		/** make a unique filename for the image and check it is not already taken if it is already taken keep trying till success */
		$now = time();

		while (File::exists($base_Dir . $beforedot . '_' . $now . '.' . $afterdot))
		{
			$now++;
		}

		/** create out of the seperated parts the new filename */
		if (self::$_foldertype == 'flags')
		{
			$filename = $beforedot . '.' . $afterdot;
		}
		else
		{
			$filename = $beforedot . '_' . $now . '.' . $afterdot;
		}

		return $filename;
	}

}
