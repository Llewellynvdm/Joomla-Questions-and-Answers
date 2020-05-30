/*--------------------------------------------------------------------------------------------------------|  www.vdm.io  |------/
    __      __       _     _____                 _                                  _     __  __      _   _               _
    \ \    / /      | |   |  __ \               | |                                | |   |  \/  |    | | | |             | |
     \ \  / /_ _ ___| |_  | |  | | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_  | \  / | ___| |_| |__   ___   __| |
      \ \/ / _` / __| __| | |  | |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __| | |\/| |/ _ \ __| '_ \ / _ \ / _` |
       \  / (_| \__ \ |_  | |__| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | |  | |  __/ |_| | | | (_) | (_| |
        \/ \__,_|___/\__| |_____/ \___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__| |_|  |_|\___|\__|_| |_|\___/ \__,_|
                                                        | |
                                                        |_|
/-------------------------------------------------------------------------------------------------------------------------------/

	@version		1.0.x
	@build			30th May, 2020
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		question_and_answer.js
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/




jQuery(document).ready(function($)
{
	var main = jQuery('#jform_main_image').val();
	if (main.length > 20)
	{
		setFile(main, false, 'main', 'image')
	}
	// set the documents
	var documents = jQuery('#jform_answer_documents').val();
	if (isJsonString(documents))
	{
		setFile(documents, false, 'answer', 'documents');
	}
});

function setFilekey(filename, fileFormat, target, type){
	var currentFileName = jQuery("#jform_"+target+"_"+type).val();
	if (currentFileName.length > 20 && (type === 'image' || type === 'document' || type === 'file')){
		// remove file from server
		removeFile_server(currentFileName, target, 2, type);
	}
	// set new key
	if ((filename.length > 20 && (type === 'image' || type === 'document' || type === 'file')) || (isJsonString(filename) && (type === 'images' || type === 'documents' || type === 'media'))){
		if((type === 'images' || type === 'documents' || type === 'media') && jQuery("#jform_id").val() == 0 && isJsonString(currentFileName)) {
			var newA = jQuery.parseJSON(currentFileName);
			var newB = jQuery.parseJSON(filename);
			var filename = JSON.stringify(jQuery.merge(newA, newB));
		}
		jQuery("#jform_"+target+"_"+type).val(filename);
		// set the FILE
		return setFile(filename, fileFormat, target, type, true);
	}
	return false;
}

function setFile(filename, fileFormat, target, type, updateName){
	if (type === 'image' || type === 'document'  || type === 'file') {
		if (!target) {
			target = filename.split('_')[0].trimLeft('.');
		}
		if (!type) {
			type = filename.split('_')[1];
		}
		if (!fileFormat) {
			fileFormat = filename.split('_')[2];
		}
		var isAre = 'is';
		// if we have a file then we must update the name with the file name
		if (updateName && type === 'file' && filename.length > 20) {
			jQuery('#jform_name').val(filename.split('VDM')[1]+'.'+fileFormat);
			jQuery('#jform_alias').val('');
		}
	} else if ((type === 'images' || type === 'documents' || type === 'media') && isJsonString(filename) ) {
		filename = jQuery.parseJSON(filename);
		if (!target) {
			target = filename[0].split('_')[0];
		}
		if (!type) {
			type = filename[0].split('_')[1];
		}
		var isAre = 'are';
	} else {
		return false;
	}
	// set icon
	if (typeof file_vector_style_abr !== 'undefined'  && fileFormat){
		var icon = 'fiv-' + file_vector_style_abr + ' fiv-icon-' + fileFormat + ' fiv-size-lg';
		var thenotice = '<div class="success-'+target+'-'+type+'-8768"><div class="uk-alert uk-alert-success" data-uk-alert><p class="uk-text-center"><span class="uk-text-bold uk-text-large"><span class="'+icon+'"></span><br />Your '+target+' '+type+' '+isAre+' set </span> </p></div>';
	} else {
		if (type === 'images' || type === 'image') {
			var icon = 'uk-icon-file-image-o';
		} else {
			var icon = 'uk-icon-file';
		}
		var thenotice = '<div class="success-'+target+'-'+type+'-8768"><div class="uk-alert uk-alert-success" data-uk-alert><p class="uk-text-center"><span class="uk-text-bold uk-text-large"><i class="'+icon+'"></i> Your '+target+' '+type+' '+isAre+' set </span> </p></div>';
	}
	var thefile = getFile(filename, fileFormat, target, type);
	jQuery("."+target+"_"+type+"_uploader").append(thenotice+thefile);
	// all is done
	return true;
}

function removeFileCheck(clearServer, target, type, uiVer){
	if (3 == uiVer) {
		UIkit.modal.confirm('Are you sure you want to delete this '+target+'?').then(function(){ removeFile(clearServer, target, 1, type); });
	} else {
		UIkit2.modal.confirm('Are you sure you want to delete this '+target+'?', function(){ removeFile(clearServer, target, 1, type); });
	}
}

function removeFile(clearServer, target, flush, type){
	if ((clearServer.length > 20 && (type === 'image' || type === 'document' || type === 'file')) || (clearServer.length > 1 && (type === 'images' || type === 'documents' || type === 'media'))){
		// remove file from server
		removeFile_server(clearServer, target, flush, type);
	}
	jQuery(".success-"+target+"-"+type+"-8768").remove();	
	// remove locally 
	if (clearServer.length > 20 && (type === 'image' || type === 'document' || type === 'file')) {
		// remove the file
		jQuery("#jform_"+target+"_"+type).val('');
	} else if (clearServer.length > 20 && (type === 'images' || type === 'documents' || type === 'media')) {
		// get the old values
		var filenames = jQuery("#jform_"+target+"_"+type).val();
		if (isJsonString(filenames)) {
			filenames = jQuery.parseJSON(filenames);
			// remove the current file from those values
			filenames = jQuery.grep(filenames, function(value) {
				return value != clearServer;
			});
			if (typeof filenames == 'object' && !jQuery.isEmptyObject(filenames)) {
				// set the new values
				var filename = JSON.stringify(filenames);
				jQuery("#jform_"+target+"_"+type).val(filename);
				setFile(filename, 0, target, type);
			} else {
				jQuery("#jform_"+target+"_"+type).val('');
			}
		} else {
			jQuery("#jform_"+target+"_"+type).val('');
		}
	}
}

function removeFile_server(currentFileName, target, flush, type){
	var getUrl = JRouter("index.php?option=com_questionsanswers&task=ajax.removeFile&format=json&raw=true&vdm="+vastDevMod);
	if(token.length > 0 && target.length > 0 && type.length > 0){
		var request = 'token='+token+'&filename='+currentFileName+'&target='+target+'&flush='+flush+'&type='+type;
	}
	return jQuery.ajax({
		type: 'GET',
		url: getUrl,
		dataType: 'json',
		data: request,
		jsonp: false
	});
}
function isJsonString(str) {
       if (typeof str != 'string') {
              str = JSON.stringify(str);
       }
       try {
               var json = jQuery.parseJSON(str);
       } catch(err) {
               return false;
       }   
       if (typeof json == 'object' && isEmpty(json)) {
              return false;
       } else if(typeof json == 'object') {
              return true;
       }
	return false;
}
function isEmpty(obj) {
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            return false;
    }
    return true;
}
 
