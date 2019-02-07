<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      upload.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
$lang = Factory::getLanguage();
$languages = LanguageHelper::getLanguages('lang_code');
$languageCode = $languages[ $lang->getTag() ]->sef;
//echo 'language is: ' . $languageCode;


?>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">
    <link href="components/com_sportsmanagement/views/imagehandler/tmpl/css/fileinput.css" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" crossorigin="anonymous">
    <link href="components/com_sportsmanagement/views/imagehandler/tmpl/themes/explorer-fas/theme.css" media="all" rel="stylesheet" type="text/css"/>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="components/com_sportsmanagement/views/imagehandler/tmpl/js/plugins/sortable.js" type="text/javascript"></script>
    <script src="components/com_sportsmanagement/views/imagehandler/tmpl/js/fileinput.js" type="text/javascript"></script>
    <script src="components/com_sportsmanagement/views/imagehandler/tmpl/js/locales/<?php echo $languageCode; ?>.js" type="text/javascript"></script>
    
    <script src="components/com_sportsmanagement/views/imagehandler/tmpl/themes/fas/theme.js" type="text/javascript"></script>
    <script src="components/com_sportsmanagement/views/imagehandler/tmpl/themes/explorer-fas/theme.js" type="text/javascript"></script>
  
  
<div class="container my-4">
<form action="<?php echo $this->request_url; ?>" enctype="multipart/form-data" id="adminForm" name="adminForm" method="post">
        
  <div class="form-group">
            <div class="file-loading">
                <input name="userfile" id="userfile" type="file" class="file" data-overwrite-initial="false" data-theme="fas">
            </div>
        </div>
  
  <input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="task" value="imagehandler.upload" />
<input type="hidden" name="folder" value="<?php echo $this->folder;?>" />
<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>

  

</div>  
  
<script>  
 jQuery("#userfile").fileinput({
        theme: 'fas',
  language: 'de',
        allowedFileExtensions: ['jpg', 'png', 'gif'],
        overwriteInitial: false,
        maxFileSize: 1000,
        maxFilesNum: 10,
        //allowedFileTypes: ['image', 'video', 'flash'],
        slugCallback: function (filename) {
            return filename.replace('(', '_').replace(']', '_');
        }
    });  

</script>
