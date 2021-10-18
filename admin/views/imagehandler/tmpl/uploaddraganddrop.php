<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagehandler
 * @file       upload.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * http://plugins.krajee.com/file-input
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;

$lang         = Factory::getLanguage();

$langname         = $lang->getName();
$languages    = LanguageHelper::getLanguages('lang_code');
//$languageCode = $languages[$lang->getTag()]->sef;

$languageCode = substr($lang->getTag(),0,2);

//echo 'lang <pre>'.print_r($lang,true).'</pre>';

//echo 'langname <pre>'.print_r($langname,true).'</pre>';
//echo 'languages <pre>'.print_r($languages,true).'</pre>';
//echo 'getTag <pre>'.print_r($lang->getTag(),true).'</pre>';
//echo 'languageCode <pre>'.print_r($languageCode,true).'</pre>';


/** https://cdnjs.com/libraries/bootstrap-fileinput */
$this->bootstrap_fileinput_version = '5.1.0';
/** https://cdnjs.com/libraries/popper.js */
//$this->bootstrap_fileinput_popperversion = '2.10.2';
$this->bootstrap_fileinput_bootstrapversion = '5.1.0';


//$bootstrap_fileinput_version = '5.1.2';

?>
<!-- bootstrap 5.x or 4.x is supported. You can also use the bootstrap css 3.3.x versions -->
<!-- link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@<?php echo $this->bootstrap_fileinput_bootstrapversion; ?>/dist/css/bootstrap.min.css" crossorigin="anonymous" -->
<!-- the fileinput plugin styling CSS file -->
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@<?php echo $this->bootstrap_fileinput_version; ?>/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />  
  
  
  
<!-- the main fileinput plugin script JS file -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@<?php echo $this->bootstrap_fileinput_version; ?>/js/fileinput.min.js"></script>
<!-- optionally if you need translation for your language then include the locale file as mentioned below (replace LANG.js with your language locale) -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@<?php echo $this->bootstrap_fileinput_version; ?>/js/locales/<?php echo $languageCode; ?>.js"></script>  
  
<div class="container my-4">
    <form action="<?php echo $this->request_url; ?>" enctype="multipart/form-data" id="adminForm" name="adminForm"
          method="post">

        <div class="form-group">
            <div class="file-loading">
                <input name="userfile" id="userfile" type="file" class="file" data-browse-on-zone-click="true">
            </div>
        </div>

        <input type="hidden" name="option" value="<?php echo $this->option; ?>"/>
        
<?php        
switch ($this->folder)
{
case 'projectteams':
?>
<input type="hidden" name="task" value="imagehandler.upload<?php echo $this->folder; ?>"/>
<?php
break;
default:
?>
<input type="hidden" name="task" value="imagehandler.upload"/>
<?php
break;
}        
?>        
        
        
        <input type="hidden" name="folder" value="<?php echo $this->folder; ?>"/>
        <input type="hidden" name="pid" value="<?php echo $this->pid; ?>"/>
        <input type="hidden" name="mid" value="<?php echo $this->mid; ?>"/>
	    <input type="hidden" name="imagelist" value="<?php echo $this->imagelist; ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>


</div>

<script>
          
// initialize with defaults
    jQuery("#userfile").fileinput(
          {
          language: '<?php echo $languageCode; ?>'  
          }
        );          
          /*
    jQuery("#userfile").fileinput({
        theme: 'fas',
        language: 'de',
        allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        overwriteInitial: false,
        maxFileSize: 8000,
        maxFilesNum: 10,
        //allowedFileTypes: ['image', 'video', 'flash'],
        slugCallback: function (filename) {
            return filename.replace('(', '_').replace(']', '_');
        }
    });
*/
</script>
