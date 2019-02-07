<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
<!-- piexif.min.js is needed for auto orienting image files OR when restoring exif data in resized images and when you 
    wish to resize images before upload. This must be loaded before fileinput.min.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/js/plugins/piexif.min.js" type="text/javascript"></script>
<!-- sortable.min.js is only needed if you wish to sort / rearrange files in initial preview. 
    This must be loaded before fileinput.min.js -->  
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/js/plugins/sortable.min.js" type="text/javascript"></script>
<!-- purify.min.js is only needed if you wish to purify HTML content in your preview for 
    HTML files. This must be loaded before fileinput.min.js -->  
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/js/plugins/purify.min.js" type="text/javascript"></script>
<!-- popper.min.js below is needed if you use bootstrap 4.x. You can also use the bootstrap js 
   3.3.x versions without popper.min.js. -->  
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<!-- the main fileinput plugin file -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/js/fileinput.min.js"></script>
<!-- optionally if you need a theme like font awesome theme you can include it as mentioned below -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/themes/fa/theme.js"></script>
<!-- optionally if you need translation for your language then include  locale file as mentioned below -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/4.4.9/js/locales/de.js"></script>
  
<div class="container kv-main">
    <div class="page-header">
        <h1>File upload demo
        </h1>
    </div>

 <form action="<?php echo $this->request_url; ?>" enctype="multipart/form-data" id="adminForm" name="adminForm"> 
         <div class="file-loading"> 
             <input id="kv-explorer" type="file" multiple> 
         </div> 
         <br> 
         <div class="file-loading"> 
             <input id="userfile" class="file" type="file" multiple data-min-file-count="1" data-theme="fas"> 
         </div> 
         <br> 
         <button type="submit" class="btn btn-primary">Submit</button> 
         <button type="reset" class="btn btn-outline-secondary">Reset</button> 
  
  <input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="task" value="imagehandler.upload" />
<input type="hidden" name="folder" value="<?php echo $this->folder;?>" />
<?php echo HTMLHelper::_( 'form.token' ); ?>
     </form> 




</div>
  
  
<script> 
             
</script> 
  
  
  
  
  
  
