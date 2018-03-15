<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      imageselect.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage helpers
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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
  $type	= JFactory::getApplication()->input->getVar( 'type' );
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
		$document = JFactory::getDocument();
    $app = JFactory::getApplication();
    self::$_foldertype = $type;
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' type<pre>'.print_r($type,true).'</pre>'),'Error');
    
		JHtml::_( 'behavior.modal' );

		$baseFolder = JURI::root();//.'images/com_sportsmanagement/database/'.ImageSelect::getfolder($type);
		$funcname = preg_replace( "/^[.]*/", '', $fieldid );

		//Build the image select functionality
		$js = "
		function selectImage_" . $type . "(image, imagename, field, fieldid)
		{
			$('a_' + field).value = 'images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+image;
			$('a_' + field + '_name').value ='images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+imagename;
			$('a_' + field + '_name').fireEvent('change');
      		if($(fieldid)) {
        		$(fieldid).value = 'images/com_sportsmanagement/database/" . self::getfolder( $type ) . "/'+imagename;
      		}
			$('a_' + field + '_name').fireEvent('change');
			//window.top.setTimeout('window.parent.SqueezeBox.close()', 100);
		}
		function reset_" . $funcname . "()
		{
			$('a_" . $fieldname . "').setProperty('value', '" . $default . "');
			$('a_" . $fieldname . "_name').setProperty('value', '" . $default . "').fireEvent('change');
		}

		function clear_" . $funcname . "()
		{
			$('a_" . $fieldname . "').setProperty('value', '');
			$('a_" . $fieldname . "_name').setProperty('value', '').fireEvent('change');
		}

		window.addEvent('domready', function()
		{
			$('a_" . $fieldname . "_name').addEvent('change', function()
			{
				if ($('a_" . $fieldname . "_name').value!='') {
					$('" . $fieldpreview_name . "').src='" . $baseFolder . "' + $('a_" . $fieldname . "_name').value;
				}
				else
				{
					$('" . $fieldpreview_name . "').src='../images/blank.png';
				}
				if($('" . $fieldid . "')) {
					$('" . $fieldid . "').value = $('a_" . $fieldname . "_name').value;
				}
			});
			$('a_" . $fieldname . "_name').fireEvent('change');
		});
		";

		$link =		'index.php?option=com_sportsmanagement&amp;view=imagehandler&amp;layout=upload&amp;type=' .
		$type . '&amp;field=' . $fieldname .'&amp;fieldid=' . $fieldid . '&amp;tmpl=component';
		$link2 =	'index.php?option=com_sportsmanagement&amp;view=imagehandler&amp;type=' .
		$type . '&amp;field=' . $fieldname . '&amp;fieldid=' . $fieldid .'&amp;tmpl=component';
		$document->addScriptDeclaration( $js );

		JHtml::_( 'behavior.modal', 'a.modal' );

		$imageselect =	"\n&nbsp;<table><tr><td><input style=\"background: #ffffff;\" type=\"text\" id=\"a_" . $fieldname . "_name\" value=\"" .
		$value . "\" disabled=\"disabled\" size=\"100\" /></td></tr>";
		$imageselect .=	"<tr><td><div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"" .
		JText::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ) . "\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 800, y: 500}}\">" .
		JText::_( 'JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE' ) . "</a></div></div>\n";
		$imageselect .=	"<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"" .
		JText::_( 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE' ) . "\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 800, y: 500}}\">" .
		JText::_( 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE' )."</a></div></div>\n";
		$imageselect .=	"<div class=\"button2-left\"><div class=\"blank\"><a title=\"" .
		JText::_( 'JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE' ) . "\" href=\"#\" onclick=\"reset_" . $fieldid . "();\">" . JText::_( 'JSEARCH_RESET' ) . "</a></div></div>\n";
		$imageselect .=	"<div class=\"button2-left\"><div class=\"blank\"><a title=\"" .
		JText::_( 'JCLEAR' ) . "\" href=\"#\" onclick=\"clear_" . $fieldid . "();\">" . JText::_( 'JCLEAR' ) . "</a></div></div></td></tr>\n";
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
$app = JFactory::getApplication();
		$params = JComponentHelper::getParams( 'com_sportsmanagement' );

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' file<pre>'.print_r($file,true).'</pre>'),'');

		$sizelimit = $params->get( 'image_max_size', 120 )*1024; //size limit in kb
		$imagesize = $file['size'];
/*
		//check if the upload is an image...getimagesize will return false if not
		if ( !getimagesize( $file['tmp_name'] ) )
		{
			JError::raiseWarning( 100, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_UPLOAD_FAILED' ) . ': ' . htmlspecialchars($file['name'], ENT_COMPAT, 'UTF-8' ) );
			return false;
		}
*/
		//check if the imagefiletype is valid
		$fileext = JFile::getExt($file['name']);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' fileext<pre>'.print_r($fileext,true).'</pre>'),'');

		$allowable	= array ('gif','jpg','jpeg','png','bmp','svg', 'GIF','JPG','JPEG','PNG','BMP','SVG');
		if ( !in_array( $fileext, $allowable ) )
		{
			JError::raiseWarning( 100, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR1' ) . ': ' . htmlspecialchars( $file['name'], ENT_COMPAT, 'UTF-8' ) );
			return false;
		}

		//Check filesize
		if ( $imagesize > $sizelimit )
		{
			JError::raiseWarning( 100, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR2' ) . ': ' . htmlspecialchars( $file['name'], ENT_COMPAT, 'UTF-8' ) );
			return false;
		}

		//XSS check
		$xss_check = JFile::read( $file['tmp_name'], false, 256 );
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
				JError::raiseWarning( 100, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_IE_WARN' ) );
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

		while( JFile::exists( $base_Dir . $beforedot . '_' . $now . '.' . $afterdot ) )
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
