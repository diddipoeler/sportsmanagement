<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      imageselect.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage helpers
 * https://css-tricks.com/examples/DragAndDropFileUploading/
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
//Factory::getDocument()->addScript(Uri::root() . '/media/media/js/mediafield.js');

/**
 * ImageSelectSM
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
abstract class ImageSelectSM
{

static $_foldertype = '';

	/**
	 * ImageSelectSM::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
  $type	= Factory::getApplication()->input->getVar( 'type' );
  self::$_foldertype = $type;
	}

	/**
	 * ImageSelectSM::getSelector()
	 * 
	 * @param mixed $fieldname
	 * @param mixed $fieldpreview_name
	 * @param mixed $type
	 * @param mixed $value
	 * @param string $default
	 * @param string $control_name
	 * @param mixed $fieldid
	 * @return
	 */
	public static function getSelector( $fieldname, $fieldpreview_name, $type, $value, $default = '', $control_name='', $fieldid)
	{
		$document = Factory::getDocument();
    $app = Factory::getApplication();
    self::$_foldertype = $type;
    
$modalheight = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('modal_popup_height', 600);
$modalwidth = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('modal_popup_width', 900);
    
		HTMLHelper::_( 'behavior.modal' );

		$baseFolder = Uri::root();//.'images/com_sportsmanagement/database/'.ImageSelect::getfolder($type);
		$funcname = preg_replace( "/^[.]*/", '', $fieldid );

		//Build the image select functionality
		$js = "
		function selectImage_" . $type . "(image, imagename, field, fieldid)
		{
			console.log('fieldid : ' + fieldid);
			console.log('field : ' + field);
			
			document.getElementById('a_' + field).value = 'images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+image;
			document.getElementById(fieldid).value ='images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+imagename;
			document.getElementById('" . $fieldid . "_preview').src = '".Uri::root()."images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+image;
var els=document.getElementsByName(field);
for (var i=0;i<els.length;i++) {
els[i].value = 'images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+imagename;}

			//$('a_' + field).value = 'images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+image;
//			$('a_' + field + '_name').value ='images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+imagename;
			//$('a_' + field + '_name').fireEvent('change');
      		//if($(fieldid)) {
        		//$(fieldid).value = 'images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+imagename;
//      		}
	//		$('a_' + field + '_name').fireEvent('change');
		}
        
		function reset_" . $funcname . "()
		{
			//jQuery('#a_" . $fieldname . "').attr('value', '" . $default . "');
			//jQuery('#a_" . $fieldname . "_name').attr('value', '" . $default . "');
			//jQuery('#a_" . $fieldname . "_name').change();
			
			var imgSource = document.getElementById('" . $fieldid . "_preview').src ;
			console.log('1.reset fieldpreview_name : ' + imgSource );
			document.getElementById('" . $fieldid . "').value = '" . $default . "';
			document.getElementById('" . $fieldid . "_preview').src = '" . Uri::root().$default . "';
			
			var imgSource = document.getElementById('" . $fieldid . "_preview').src ;
			console.log('2.reset fieldpreview_name : ' + imgSource );
			console.log('3.reset default : ' + '" . $default . "' );
		}

		function clear_" . $funcname . "()
		{
			//alert('hallo');
			//jQuery('#a_" . $fieldname . "').attr('value', '');
			//select = document.getElementById('a_" . $fieldname . "_name').value;
			console.log('fieldname: ' + '" . $fieldname . "' );
			console.log('fieldpreview_name : ' + '" . $fieldpreview_name . "' );
			//alert(select);
			document.getElementById('" . $fieldid . "').value = '".Uri::root().sportsmanagementHelper::getDefaultPlaceholder($type)."';
			document.getElementById('" . $fieldid . "_preview').src = '".Uri::root().sportsmanagementHelper::getDefaultPlaceholder($type)."';
			//jQuery('#a_" . $fieldname . "_name').val('');
			//jQuery('#" . $fieldpreview_name . "').attr('src', '');
//			jQuery('#a_" . $fieldname . "_name').change();
		}


			
			
		//window.addEvent('domready', function()
        jQuery(document).ready(function()
		{
			
		select = document.getElementById('" . $fieldid . "').value;
		console.log('select : ' + select  );
		document.getElementById('" . $fieldid . "_preview').src = '" . Uri::root(). "' + select  ;

		console.log('ready: ' + jQuery('#a_" . $fieldname . "_name').val() );	
		console.log('fieldname: ' + '" . $fieldname . "' );
		console.log('fieldid: ' + '" . $fieldid . "' );
		console.log('type: ' + '" . $type . "' );
		console.log('preview name: ' + '" . $fieldpreview_name . "' );
		
//			jQuery('#a_" . $fieldname . "_name').change( function()
//			{
//			alert('hallo');
//				if (jQuery('#a_" . $fieldname . "_name').val() != '') {
//					jQuery('#" . $fieldpreview_name . "').src='" . $baseFolder . "' + jQuery('#a_" . $fieldname . "_name').val();
//				}
//				else
//				{
//					jQuery('#" . $fieldpreview_name . "').src='../images/blank.png';
//				}
//				if(jQuery('" . $fieldid . "')) {
//					jQuery('" . $fieldid . "').val(jQuery('#a_" . $fieldname . "_name').val()) ;
//				}
//			});
//			jQuery('#a_" . $fieldname . "_name').change();
		});
		";
      
$js .= '      
// Compatibility with mootools modal layout
function jInsertFieldValue(value, id) {
console.log("value : " + value);
console.log("id : " + id);	
jQuery("#" + id).val(value);
select = jQuery("#" + id).val();

console.log("select : " + select);
var $img = jQuery("#" + id + "_preview");

console.log("img : " + $img);	

//if (select) {
			$img.attr("src", "' . Uri::root(). '" + select);
//			jQuery("#' . $fieldpreview_name . '").hide();
//			jQuery("#' . $fieldpreview_name . '").show()
//		}
//document.getElementById("' . $fieldpreview_name . '").src = "' . Uri::root(). '" + select  ;

}      
';

$imageselect = '';
if ( ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('cfg_draganddrop') )
{	
$layoutdrag = 'uploaddraganddrop';

//$imageselect .= '<button id="drag'.$fieldid.'" class="btn btn-primary">Open dialog</button>';	
//$imageselect .= '<div id="outputdrag'.$fieldid.'"></div>';	
//$imageselect .= '
// <script type="text/javascript">
//        jQuery("#drag'.$fieldid.'").click(function() {
//            jQuery.FileDialog({multiple: true}).on("files.bs.filedialog", function(ev) {
//                var files = ev.files;
//                var text = "";
//                files.forEach(function(f) {
//                    text += f.name + "<br/>";
//                });
//                jQuery("#outputdrag'.$fieldid.'").html(text);
//            }).on("cancel.bs.filedialog", function(ev) {
//                jQuery("#outputdrag'.$fieldid.'").html("Cancelled!");
//            });
//        });
//        </script>';	
	
	
}
else
{
$layoutdrag = 'upload';
}
		
		$link =	'index.php?option=com_sportsmanagement&amp;view=imagehandler&amp;layout='.$layoutdrag.'&amp;type=' .
		$type . '&amp;field=' . $fieldname .'&amp;fieldid=' . $fieldid . '&amp;tmpl=component';
		
//        $link2 = 'index.php?option=com_sportsmanagement&amp;view=imagehandler&amp;type=' .
//		$type . '&amp;field=' . $fieldname . '&amp;fieldid=' . $fieldid .'&amp;tmpl=component';
        
       $link2 = 'index.php?option=com_media&amp;view=images' .
		'&amp;asset=com_sportsmanagement&amp;folder=com_sportsmanagement/database/' . self::getfolder( $type ) . '&author=&amp;fieldid=' . $fieldid .'&amp;tmpl=component';
        
        
/*		
if (version_compare(JSM_JVERSION, '4', 'eq')) {
$link2 = 'index.php?option=com_media&tmpl=component&path=local-0:/com_sportsmanagement/database/'.$type;
} else {
$link2 = 'index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/'.$type;
}   		
*/		
		
		$document->addScriptDeclaration( $js );

		HTMLHelper::_( 'behavior.modal', 'a.modal' );

		$imageselect .=	"\n&nbsp;<table><tr><td><input style=\"background: #ffffff;\" type=\"text\" id=\"" . $fieldid . "\" name=\"" . $fieldname . "\"  value=\"" .
		$value . "\" disabled=\"disabled\" size=\"100\" /></td></tr>";
		$imageselect .=	"<tr><td><div class=\"button2-left\"><div class=\"blank\">";
$imageselect .=	 sportsmanagementHelper::getBootstrapModalImage('upload'.$funcname ,'',Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'),'20',Uri::base().$link,$modalwidth,$modalheight);   		
		$imageselect .=	 "</div></div>\n";
/*
$imageselect .=	 '	
<script type="text/javascript">
jQuery(document).ready(function($){
	$("dt").click(function(){ 
		$("dd").slideUp("fast");
		$("dt > a").removeClass("open").addClass("closed");
		$(this).next("dd").slideDown("fast"); 
		$(this).children("a").removeClass("closed").addClass("open");
	});
});
</script>

<style type="text/css">
body {background:#ffffff;}
dd { display:none; }
.closed { background:red; }
.open { background:green; }
</style>	
<dl>
  <dt><a href="#" class="closed">'.Text::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ).'</a></dt>
  <dd>
  upload
  </dd>
  
</dl>


';	
*/		
		
/*		
$imageselect .=	HTMLHelper::_('bootstrap.startAccordion', 'drag'.$funcname);
$imageselect .=	HTMLHelper::_('bootstrap.addSlide', 'drag'.$funcname, Text::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ), 'drag'.$funcname);		
$imageselect .=	HTMLHelper::_('bootstrap.endSlide');		
$imageselect .=	HTMLHelper::_('bootstrap.endAccordion');
*/

/*		
$imageselect .=	 '		
<div class="panel-group" id="drag'.$funcname.'">		
		
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#drag'.$funcname.'" href="#drag'.$funcname.'">'.Text::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ).'</a>
</h4>

</div>
<div id="drag'.$funcname.'" class="panel-collapse collapse">            
<div class="panel-body"> 
hochladen
</div>	
</div>	
</div>
</div>';
*/		
		/*
		$imageselect .=	"<tr><td><div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"" .
		Text::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ) . "\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 800, y: 500}}\">" .
		Text::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ) . "</a></div></div>\n";
		*/
		$imageselect .=	"<div class=\"button2-left\"><div class=\"blank\">";
$imageselect .=	 sportsmanagementHelper::getBootstrapModalImage('select'.$funcname ,'',Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),'20',Uri::base().$link2,$modalwidth,$modalheight);   		
		$imageselect .=	 "</div></div>\n";
		/*
		$imageselect .=	"<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"" .
		Text::_( 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE' ) . "\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 800, y: 500}}\">" .
		Text::_( 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE' )."</a></div></div>\n";
		*/
		
		$imageselect .=	"<div class=\"button2-left\"><div class=\"blank\"><button id='reset" . $fieldid . "' class='btn btn-primary'><a title=\"" .
		Text::_( 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE' ) . "\" href=\"#\" onclick=\"reset_" . $fieldid . "();\">" . Text::_( 'JSEARCH_RESET' ) . "</a></button></div></div>\n";
		
        $imageselect .=	"<div class=\"button2-left\"><div class=\"blank\"><button id='clear" . $fieldid . "' class='btn btn-primary'><a title=\"" .
		Text::_( 'JCLEAR' ) . "\" href=\"#\" onclick=\"clear_" . $fieldid . "();\">" . Text::_( 'JCLEAR' ) . "</a></button></div></div>";
        
        $imageselect .=	"</td></tr>\n";
		$imageselect .=	"\n<tr><td><input type=\"hidden\" id=\"a_" . $fieldname . "\" name=\"" . $fieldname . "\" value=\"" . $value."\" /></td></tr></table>";

		return $imageselect;
	}


	/**
	 * ImageSelectSM::check()
	 * 
	 * @param mixed $file
	 * @return
	 */
	public static function check( $file )
	{
		jimport( 'joomla.filesystem.file' );
$app = Factory::getApplication();
		$params = ComponentHelper::getParams( 'com_sportsmanagement' );



		$sizelimit = $params->get( 'image_max_size', 120 )*1024; //size limit in kb
		$imagesize = $file['size'];
/*
		//check if the upload is an image...getimagesize will return false if not
		if ( !getimagesize( $file['tmp_name'] ) )
		{
			JError::raiseWarning( 100, Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_UPLOAD_FAILED' ) . ': ' . htmlspecialchars($file['name'], ENT_COMPAT, 'UTF-8' ) );
			return false;
		}
*/
		//check if the imagefiletype is valid
		$fileext = File::getExt($file['name']);



		$allowable	= array ('gif','jpg','jpeg','png','bmp','svg', 'GIF','JPG','JPEG','PNG','BMP','SVG');
		if ( !in_array( $fileext, $allowable ) )
		{
			JError::raiseWarning( 100, Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR1' ) . ': ' . htmlspecialchars( $file['name'], ENT_COMPAT, 'UTF-8' ) );
			return false;
		}

		//Check filesize
		if ( $imagesize > $sizelimit )
		{
			JError::raiseWarning( 100, Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR2' ) . ': ' . htmlspecialchars( $file['name'], ENT_COMPAT, 'UTF-8' ) );
			return false;
		}

		//XSS check
		$xss_check = File::read( $file['tmp_name'], false, 256 );
		$html_tags = array( 'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big',
							'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col',
							'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn',
							'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe',
							'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext',
							'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript',
							'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp',
							'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style',
							'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var',
							'wbr', 'xml', 'xmp', '!DOCTYPE', '!--' );
		foreach( $html_tags as $tag )
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if ( stristr( $xss_check, '<' . $tag . ' ') || stristr( $xss_check, '<' . $tag . '>' ) )
			{
				JError::raiseWarning( 100, Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_IE_WARN' ) );
				return false;
			}
		}

		return true;
	}

	/**
	 * Sanitize the image file name and return an unique string
	 *
	 * @since 0.9
	 * @author Christoph Lukes
	 *
	 * @param string $base_Dir the target directory
	 * @param string $filename the unsanitized imagefile name
	 *
	 * @return string $filename the sanitized and unique image file name
	 */
	public static function sanitize( $base_Dir, $filename )
	{
		jimport( 'joomla.filesystem.file' );
    
    
		//check for any leading/trailing dots and remove them (trailing shouldn't be possible cause of the getEXT check)
		$filename = preg_replace( "/^[.]*/", '', $filename );
		$filename = preg_replace( "/[.]*$/", '', $filename ); //shouldn't be necessary, see above

		//we need to save the last dot position cause preg_replace will also replace dots
		$lastdotpos = strrpos( $filename, '.' );

		//replace invalid characters
		$chars = '[^0-9a-zA-Z()_-]';
		$filename	 = strtolower( preg_replace( "/$chars/", '_', $filename ) );

		//get the parts before and after the dot (assuming we have an extension...check was done before)
		$beforedot	= substr( $filename, 0, $lastdotpos );
		$afterdot	 = substr( $filename, $lastdotpos + 1 );

		//make a unique filename for the image and check it is not already taken
		//if it is already taken keep trying till success
		$now = time();

		while( File::exists( $base_Dir . $beforedot . '_' . $now . '.' . $afterdot ) )
		{
			$now++;
		}

		//create out of the seperated parts the new filename
		if ( self::$_foldertype == 'flags' )
		{
    $filename = $beforedot . '.' . $afterdot;
    }
    else
    {
		$filename = $beforedot . '_' . $now . '.' . $afterdot;
    }
    
		return $filename;
	}

	/**
	 * ImageSelectSM::getfolder()
	 * 
	 * @param mixed $type
	 * @return
	 */
	static function getfolder( $type )
	{
		switch( $type )
		{
			case "clubs_small":
				return "clubs/small";
				break;
			case "clubs_medium":
				return "clubs/medium";
				break;
			case "clubs_large":
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
				return "persons";
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
                
            case "agegroups":
				return "agegroups";
				break;    
				
			default:
				return "events/".$type;
		}
	}

}
?>
