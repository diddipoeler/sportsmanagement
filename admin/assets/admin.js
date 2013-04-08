function CbetCommunity()
{
	this.resetprivacy	= function(){
		var profilePrivacy	= joms.jQuery('input[name=privacyprofile]:checked').val();
		var friendPrivacy	= joms.jQuery('input[name=privacyfriends]:checked').val();
		var photoPrivacy	= joms.jQuery('input[name=privacyphotos]:checked').val();
		
		jax.call( 'cbe' , 'admin,configuration,ajaxResetPrivacy' , photoPrivacy , profilePrivacy , friendPrivacy );
	}
	
	this.redirect		= function( url ){
		window.location.href = url;
	}
	
	this.removeOption	= function(){
		$('options').getElements('option').each(function(element, count){
			if(element.selected){
				element.remove();
				
				// Remove this value's from the hidden form so that when the user saves,
				// this element which is removed will not be saved.
				var childrens	= $('childrens').value.split(',');
				
				childrens.splice(childrens.indexOf(element.value), 1);
				
				$('childrens').value	= childrens.join();
				
				//console.log(chil);
				//console.log(childrens.splice(childrens.indexOf(element.value), 1).join());
				
				
			}
		});
		
		//console.log(childrens);
	}
	
	this.showAddOption	= function(){
		
		if($('showOption').getStyle('display') == 'none'){
			$('showOption').setStyle('display','inline');	
			$('hideOption').setStyle('display','none');
			$('addOption').setStyle('display','none');
		} else {
			$('showOption').setStyle('display','none');
			$('hideOption').setStyle('display','inline');
			$('addOption').setStyle('display','inline');
		}
		//alert($('addOption').getStyle('display'));
		//$('addOption').setStyle('display','block');
	}
	
	this.saveGroupCategory	= function(){
		var values	= jax.getFormValues('editGroupCategory');
		
		jax.call('cbe','admin,groupcategories,ajaxSaveCategory', values);
	}
	
	this.editGroupCategory	= function(isEdit , windowTitle){
		var ajaxCall	= 'jax.call("cbe","admin,groupcategories,ajaxEditCategory" , ' + isEdit + ');';

		cWindowShow(ajaxCall , windowTitle , 430 , 280);
	}
	
	this.saveVideosCategory	= function(){
		var values	= jax.getFormValues('editVideosCategory');

		jax.call('cbe','admin,videoscategories,ajaxSaveCategory', values);
	}

	this.editVideosCategory	= function(isEdit , windowTitle){
		var ajaxCall	= 'jax.call("cbe","admin,videoscategories,ajaxEditCategory" , ' + isEdit + ');';

		cWindowShow(ajaxCall , windowTitle , 430 , 280);
	}
	
	this.newField = function(){

		cWindowShow('jax.call("cbe","admin,profiles,ajaxEditField","0");', '' , 650 ,420 );
	
		return false;
	}

	this.newFieldGroup = function(){

		cWindowShow('jax.call("cbe","admin,profiles,ajaxEditGroup","0");', '' , 450 ,200 );
	
		return false;
	}
	
	this.editField = function( id , title )
	{
		cWindowShow( 'jax.call("cbe", "admin,profiles,ajaxEditField", "' + id + '");' , '' , 650 , 420 );
		return false;
	}

	this.editFieldGroup = function( id , title )
	{
		cWindowShow( 'jax.call("cbe", "admin,profiles,ajaxEditGroup", "' + id + '");' , '' , 450 , 200 );
		return false;
	}

	this.addOption = function(parent){
	
		var addable = $('options').getElements('option').every( function(element, count){
			if(element.value == $('newoption').value){
				return false
			}
			return true;
		});
	
		if(addable){
			var el = new Element('option', {'value': $('newoption').value});
			
			el.setHTML($('newoption').value);
			el.setProperty('value', '0');
			
			// Clone element to the 'defaults' select list
			var defaultEl	= el.clone();
			
			el.injectInside($('options'));
			defaultEl.injectInside($('default'));
			// If parent is 0 we know this is a new record, so we dont add the options 
			// in the database yet. We should only add the options once a user hit the 'save' button.
	// 		if(parent != 0 || parent != '0'){
	// 			// Call ajax function to add the options for this parent item.
	// 			jax.call('cbe','cxAddOption', $('newoption').value, parent);
	// 		}
		} else {
			$('ajaxResponse').setHTML('Option exists');
		}
	
	}
	
	this.togglePublish	= function( ajaxTask , id , type ){
		jax.call( 'cbe' , 'admin,' + ajaxTask , id , type );
	}
	
	this.changeType = function(type){
// 		if( type == 'group' )
// 		{
// 			$$('.fieldGroups').setStyle('display', 'none');
// 		}
// 		else
// 		{
			$$('.fieldGroups').setStyle('display', 'table-row');
// 		}
		
		if( type == 'select' || type == 'singleselect' || type == 'radio' || type == 'list' || type == 'checkbox' )
// 		if(type == 'text' || type == 'group' || type == 'textarea' || type =='date' )
		{
			$$('.fieldSizes').setStyle('display', 'none');
			$$('.fieldOptions').setStyle('display', 'table-row');
		}
		else
		{
			$$('.fieldOptions').setStyle('display', 'none');
			if( type == 'text' || type == 'textarea' )
			{
				$$('.fieldSizes').setStyle('display', 'table-row');
			}
			else
			{
				$$('.fieldSizes').setStyle('display', 'none');
			}
		}
		jax.call( 'cbe' , 'admin,profiles,ajaxGetFieldParams' , type );
	
	}
	
	this.insertParams = function( val ){
		joms.jQuery( '#fieldParams' ).html( val );
	}
	
	this.saveField = function(id){
		var values = jax.getFormValues('editField');

		jax.call('cbe','admin,profiles,ajaxSaveField', id , values);
	}

	this.saveFieldGroup = function(id){
		var values = jax.getFormValues('editField');

		jax.call('cbe','admin,profiles,ajaxSaveGroup', id , values);
	}
		
	this.showRemoveOption = function(){
		if($('addOption').getStyle('display') == 'inline'){
			// Hide the add option and show the remove option
			$('removeOption').setStyle('display','inline');
			$('addOption').setStyle('display','none');
		}
	}
	
	this.updateAttribute = function(id, type){
		jax.call('cbe','cxUpdateAttribute', id, type, $(type + id).value);
	}

	this.changeTemplate = function( templateName ){
		jax.call( 'cbe' , 'admin,templates,ajaxChangeTemplate' , templateName );
	}
	
	this.editTemplate = function( templateName , fileName , override ){
		jax.call( 'cbe' , 'admin,templates,ajaxLoadTemplateFile', templateName, fileName , override );
	}
	
	this.resetTemplateForm = function(){
		joms.jQuery('#data').val('');
		joms.jQuery('#filePath').html('');
	}
	
	this.resetTemplateFiles = function(){
		joms.jQuery('#templates-files-container').html('');
	}
	
	this.saveTemplateFile = function( override ){
		var fileData		= joms.jQuery( '#data' ).val();
		var fileName		= joms.jQuery( '#fileName' ).val();
		var templateName	= joms.jQuery( '#templateName' ).val();

		jax.call('cbe', 'admin,templates,ajaxSaveTemplateFile', templateName , fileName, fileData , override );
	}

	this.assignGroup = function( memberId ){
		cWindowShow('jax.call("cbe","admin,groups,ajaxAssignGroup", ' + memberId + ');', '' , 550 , 170 );
	}

	this.saveAssignGroup = function( memberId ){
		var group	= joms.jQuery('#groupid').val();
		
		if( group == '-1' )
		{
			joms.jQuery('#group-error-message').html('Please select a group');
			return false;
		}
		joms.jQuery('#assignGroup').submit();
	}
	
	this.editGroup = function( groupId ){
		cWindowShow('jax.call("cbe","admin,groups,ajaxEditGroup", ' + groupId + ');', 'Editing Group' , 550 , 450 );
	}
	
	this.changeGroupOwner = function( groupId ){
		cWindowShow('jax.call("cbe","admin,groups,ajaxChangeGroupOwner",' + groupId + ');', 'Change Group Owner' , 480 , 250 );
	}
	
	this.saveGroup = function(){
		joms.jQuery('#editgroup').submit();
	}

	this.saveGroupOwner = function(){
		document.forms['editgroup'].submit();
	}
	
	this.checkVersion = function(){	
		cWindowShow('jax.call("cbe","admin,about,ajaxCheckVersion");', 'CbeCommunity' , 450 , 200 );
	}
	
	this.reportAction = function( actionId, ignore ){
		cWindowShow( 'jax.call("cbe","admin,reports,ajaxPerformAction", "' + actionId + '", "' + ignore + '");' , 'Report' , 450 , 200 );
	}
	
	this.ruleScan = function(){
		cWindowShow('jax.call("cbe","admin,userpoints,ajaxRuleScan");', 'User Rule Scan' , 450 ,400 );
		return false;
	}
		
	this.editRule = function( ruleId ){
		cWindowShow( 'jax.call("cbe","admin,userpoints,ajaxEditRule","' + ruleId + '");' , 'Edit Rule' , 450 , 300 );
		return false;
	}
	
	this.saveRule = function( ruleId ){				
		var values = jax.getFormValues('editRule');
		jax.call('cbe','admin,userpoints,ajaxSaveRule', ruleId , values);
	}
	
	this.updateField = function (sourceId, targetId){
		joms.jQuery('#' + targetId).val( jQuery('#' + sourceId).val() );
	}
	
	this.editEvent = function( eventId ){
		cWindowShow('jax.call("cbe","admin,events,ajaxEditEvent", ' + eventId + ');', 'Editing Event' , 450 , 350 );
	}
	
	this.saveEvent = function(){
		joms.jQuery('#editevent').submit();
	}
	
	this.editEventCategory = function( catId , windowTitle ){
		cWindowShow('jax.call("cbe","admin,eventcategories,ajaxEditCategory", ' + catId + ');', windowTitle, 450 , 350 );
	}
	
	this.saveEventCategory	= function(){
		var values	= jax.getFormValues('editEventCategory');
		jax.call('cbe','admin,eventcategories,ajaxSaveCategory', values);
	}
	
	this.toggleMultiProfileChild = function( fieldId ){
		var element	= '#publish' + fieldId;
		var image	= "images/tick.png";
		var hidden	= '';
		
		if( joms.jQuery( element ).children('input[@name=fields]').val() )
		{
			image	= "images/publish_x.png";
		}
		else
		{
			hidden	= '<input type="hidden" name="fields[]" value="' + fieldId + '" />';
		}
		var val	= '<a href="javascript:void(0);" onclick="ccommunity.toggleMultiProfileChild('+ fieldId + ');"><img src="' + image + '"/></a>' + hidden;

		joms.jQuery( element ).html( val );
	}
	
	this.registerZencoderAccount	= function(){
		cWindowShow('jax.call("cbe","admin,zencoder,ajaxShowForm");', '' , 400 ,220 );
		return false;
	}
	
	this.submitZencoderAccount	= function(){
		var values	= jax.getFormValues('registerZencoderAccount');
		jax.call('cbe','admin,zencoder,ajaxSubmitForm', values);
	}
	
	/**
	 * Used by Joomla elements such as the 'users' element
	 **/	 	
	this.selectUser = function( id , title , object ){
		document.getElementById(object + '_id').value = id;
		document.getElementById(object + '_name').value = title;
		document.getElementById('sbox-window').close();
	}
}

var ccommunity = new CbetCommunity();

if( typeof( Joomla ) != 'object' )
{
	var Joomla	= new Object();
}