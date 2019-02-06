<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      uploaddraganddrop.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 * https://github.com/blueimp/jQuery-File-Upload/wiki
 */
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/vendor/jquery.ui.widget.js');

$this->document->addScript('https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js');
$this->document->addScript('https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js');

$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.iframe-transport.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-process.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-image.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-audio.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-video.js');
$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/fileupload/jquery.fileupload-validate.js');

$this->document->addStyleSheet(Uri::root() .'administrator/components/com_sportsmanagement/assets/css/fileupload/style.css', 'text/css');
$this->document->addStyleSheet(Uri::root() .'administrator/components/com_sportsmanagement/assets/css/fileupload/jquery.fileupload.css', 'text/css');

echo 'folder '.$this->folder;

$uploadhandler = Uri::root() .'images/com_sportsmanagement/database/'.$this->folder;
echo 'uploadhandler  '.$uploadhandler ;
?>



<script>
/*jslint unparam: true, regexp: true */
/*global window, $ */
jQuery(function ($) {
    'use strict';
    // Change this to the location of your server-side upload handler:
    var url = window.location.hostname === '<?php echo $uploadhandler;?>',
        uploadButton = $('<button/>')
            .addClass('btn btn-primary')
            .prop('disabled', true)
            .text('Processing...')
            .on('click', function () {
                var $this = $(this),
                    data = $this.data();
                $this
                    .off('click')
                    .text('Abort')
                    .on('click', function () {
                        $this.remove();
                        data.abort();
                    });
                data.submit().always(function () {
                    $this.remove();
                });
            });
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<div/>').appendTo('#files');
        $.each(data.files, function (index, file) {
            var node = $('<p/>')
                    .append($('<span/>').text(file.name));
            if (!index) {
                node
                    .append('<br>')
                    .append(uploadButton.clone(true).data(data));
            }
            node.appendTo(data.context);
        });
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
        if (file.preview) {
            node
                .prepend('<br>')
                .prepend(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
        if (index + 1 === data.files.length) {
            data.context.find('button')
                .text('Upload')
                .prop('disabled', !!data.files.error);
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url);
                $(data.context.children()[index])
                    .wrap(link);
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
</script>

<div class="container">
    <h1>jQuery File Upload Demo</h1>
    <h2 class="lead">Basic Plus version</h2>
    <ul class="nav nav-tabs">
        
        <li class="active"><a href="basic-plus.html">Basic Plus</a></li>
        
    </ul>
    <br>
    <blockquote>
        <p>File Upload widget with multiple file selection, drag&amp;drop support, progress bar, validation and preview images, audio and video for jQuery.<br>
        Supports cross-domain, chunked and resumable file uploads and client-side image resizing.<br>
        Works with any server-side platform (PHP, Python, Ruby on Rails, Java, Node.js, Go etc.) that supports standard HTML form file uploads.</p>
    </blockquote>
    <br>
    <!-- The fileinput-button span is used to style the file input field as button -->
    <span class="btn btn-success fileinput-button">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Add files...</span>
        <!-- The file input field used as target for the file upload widget -->
        <input id="fileupload" type="file" name="files[]" multiple>
    </span>
    <br>
    <br>
    <!-- The global progress bar -->
    <div id="progress" class="progress">
        <div class="progress-bar progress-bar-success"></div>
    </div>
    <!-- The container for the uploaded files -->
    <div id="files" class="files"></div>
    <br>
    
</div>















