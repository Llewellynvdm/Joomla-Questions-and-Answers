<?php
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
	@build			27th December, 2018
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		edit.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
$componentParams = $this->params; // will be removed just use $this->params instead
?>
<script type="text/javascript">
	// waiting spinner
	var outerDiv = jQuery('body');
	jQuery('<div id="loading"></div>')
		.css("background", "rgba(255, 255, 255, .8) url('components/com_questionsanswers/assets/images/import.gif') 50% 15% no-repeat")
		.css("top", outerDiv.position().top - jQuery(window).scrollTop())
		.css("left", outerDiv.position().left - jQuery(window).scrollLeft())
		.css("width", outerDiv.width())
		.css("height", outerDiv.height())
		.css("position", "fixed")
		.css("opacity", "0.80")
		.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
		.css("filter", "alpha(opacity = 80)")
		.css("display", "none")
		.appendTo(outerDiv);
	jQuery('#loading').show();
	// when page is ready remove and show
	jQuery(window).load(function() {
		jQuery('#questionsanswers_loader').fadeIn('fast');
		jQuery('#loading').hide();
	});
</script>
<div id="questionsanswers_loader" style="display: none;">
<form action="<?php echo JRoute::_('index.php?option=com_questionsanswers&layout=edit&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<div class="form-horizontal">
	<div class="span9">

	<?php echo JHtml::_('bootstrap.startTabSet', 'question_and_answerTab', array('active' => 'details')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'question_and_answerTab', 'details', JText::_('COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_DETAILS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo JLayoutHelper::render('question_and_answer.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'question_and_answerTab'; ?>
	<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('question_and_answer.delete') || $this->canDo->get('question_and_answer.edit.created_by') || $this->canDo->get('question_and_answer.edit.state') || $this->canDo->get('question_and_answer.edit.created')) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'question_and_answerTab', 'publishing', JText::_('COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('question_and_answer.publishing', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('question_and_answer.metadata', $this); ?>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'question_and_answerTab', 'permissions', JText::_('COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_PERMISSION', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<fieldset class="adminform">
					<div class="adminformlist">
					<?php foreach ($this->form->getFieldset('accesscontrol') as $field): ?>
						<div>
							<?php echo $field->label; echo $field->input;?>
						</div>
						<div class="clearfix"></div>
					<?php endforeach; ?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="question_and_answer.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	</div>
</div><div class="span3">
	<?php echo JLayoutHelper::render('question_and_answer.details_rightside', $this); ?>
</div>

<div class="clearfix"></div>
<?php echo JLayoutHelper::render('question_and_answer.details_under', $this); ?>
</form>
</div>

<script type="text/javascript">




<?php if ($formats = $this->params->get('image_formats', null)) : ?>
	<?php $uikitVersion = $this->params->get('uikit_version', 2);  // get Uikit Version ?>
	// set some details
	var sizeNotice = '';
	<?php if ($resize = $this->params->get('crop_main', null)) : ?>
		var sizemain = '(';
		<?php if ($width = $this->params->get('main_width', null)): ?>
			sizemain += 'width: <?php echo $width; ?>px';
		<?php else: ?>
			sizemain += 'width: <?php echo JText::_('COM_QUESTIONSANSWERS_PROPORTIONALLY'); ?>';
		<?php endif; ?>
		<?php if ($height = $this->params->get('main_height', null)): ?>
			sizemain += '  height: <?php echo $height; ?>px';
		<?php else: ?>
			sizemain += '  height: <?php echo JText::_('COM_QUESTIONSANSWERS_PROPORTIONALLY'); ?>';
		<?php endif; ?>
		sizemain += ')';
		<?php if (2 == $uikitVersion) : ?>
			sizeNotice = '<span data-uk-tooltip title="<?php echo JText::_('COM_QUESTIONSANSWERS_THE_MAIN_WILL_BE_CROPPED_TO_THIS_SIZE'); ?>">'+sizemain+'</span>';
		<?php else: ?>
			sizeNotice = '<span uk-tooltip title="<?php echo JText::_('COM_QUESTIONSANSWERS_THE_MAIN_WILL_BE_CROPPED_TO_THIS_SIZE'); ?>">'+sizemain+'</span>';
		<?php endif; ?>
	<?php endif; ?>
	// load the UIKIT script
	<?php if (2 == $uikitVersion) : ?>
	// load uikit 2 uploader script
	jQuery(function($){
		// prep the placeholder uploading divs
		$('#uikittwo-main-image-uploader').show();
		$('#uikitthree-main-image-uploader').remove();
		$('#error-main-image-uploader').remove();
		$('#size-main').html(sizeNotice);
		$('#main-image-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
		// get progressbar
		var progressbar = $("#uikittwo-progressbar-main-image"),
		bar         = progressbar.find('.uk-progress-bar'),
		settings    = {

			action: JRouter('index.php?option=com_questionsanswers&task=ajax.uploadfile&format=json&type=image&target=main&raw=true&token='+token+'&vdm='+vastDevMod), // upload url

			allow : '*.(<?php echo implode('|', $formats); ?>)', // allow uploads

			loadstart: function() {
				jQuery(".success-main-image-8768").remove();
				bar.css("width", "0%").text("0%");
				progressbar.removeClass("uk-hidden");
			},

			progress: function(percent) {
				percent = Math.ceil(percent);
				bar.css("width", percent+"%").text(percent+"%");
			},

			allcomplete: function(response) {
				bar.css("width", "100%").text("100%");
				response = JSON.parse(response);
				setTimeout(function(){
					progressbar.addClass("uk-hidden");
				}, 250);
				if (response.error){
					alert(response.error);
				} else if (response.success) {
					// set the new file name and if another is found delete it
					setFilekey(response.success, response.fileformat, 'main', 'image');
				}
			}
		};

		var select = UIkit2.uploadSelect($("#uikittwo-upload-select-main-image"), settings),
		drop   = UIkit2.uploadDrop($("#uikittwo-upload-drop-main-image"), settings);
	});
	<?php else: ?>
	// load uikit 3 uploader script
	jQuery(function($){
		// prep the placeholder uploading divs
		$('#uikitthree-main-image-uploader').show();
		$('#uikittwo-main-image-uploader').remove();
		$('#error-main-image-uploader').remove();
		$('#size-main').html(sizeNotice);
		$('#main-image-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
		// get progressbar
		var bar = document.getElementById('uikitthree-progressbar-main-image');
		UIkit.upload('#uikitthree-upload-main-image', {

	 		url: JRouter('index.php?option=com_questionsanswers&task=ajax.uploadfile&format=json&type=image&target=main&raw=true&token='+token+'&vdm='+vastDevMod), // upload url
			multiple: true,
			allow : '*.(<?php echo implode('|', $formats); ?>)', // allow uploads

	 		beforeSend: function (environment) {
				// console.log('beforeSend', arguments);

				// The environment object can still be modified here. 
				// var {data, method, headers, xhr, responseType} = environment;
			},
			beforeAll: function () {
	       			// console.log('beforeAll', arguments);
			},
			load: function () {
				// console.log('load', arguments);
			},
			error: function () {
				// console.log('error', arguments);
			},
			complete: function () {
				// console.log('complete', arguments);
			},

			loadStart: function (e) {
				jQuery(".success-main-image-8768").remove();

				bar.removeAttribute('hidden');
				bar.max = e.total;
				bar.value = e.loaded;
			},

			progress: function (e) {
				bar.max = e.total;
				bar.value = e.loaded;
			},

			loadEnd: function (e) {
				bar.max = e.total;
				bar.value = e.loaded;
			},

			completeAll: function (response) {
				setTimeout(function () {
	 				bar.setAttribute('hidden', 'hidden');
				}, 250);
				// act upon the response
				if (response.response) {
					response = JSON.parse(response.response);
					if (response.error){
						alert(response.error);
					} else if (response.success) {
						// set the new file name and if another is found delete it
						setFilekey(response.success, response.fileformat, 'main', 'image');
					}
				}
	  		}

		});
	});
	<?php endif; ?>
<?php else: ?>
	jQuery('#error-main-image-uploader').html('<b><?php echo JText::_('COM_QUESTIONSANSWERS_ALLOWED_IMAGE_FORMATS_ARE_NOT_SET_IN_THE_GLOBAL_SETTINGS_PLEASE_NOTIFY_YOUR_SYSTEM_ADMINISTRATOR'); ?></b>');
<?php endif; ?>

<?php if ($formats = $this->params->get('document_formats', null)) : ?>
	<?php $uikitVersion = $this->params->get('uikit_version', 2);  // get Uikit Version ?>
	// load the UIKIT script
	<?php if (2 == $uikitVersion) : ?>
	// load uikit 2 uploader script
	jQuery(function($){
		// prep the placeholder uploading divs
		$('#uikittwo-answer-documents-uploader').show();
		$('#uikitthree-answer-documents-uploader').remove();
		$('#error-answer-documents-uploader').remove();
		$('#answer-documents-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
		// get progressbar
		var progressbar = $("#uikittwo-progressbar-answer-documents"),
		bar         = progressbar.find('.uk-progress-bar'),
		settings    = {

			action: JRouter('index.php?option=com_questionsanswers&task=ajax.uploadfile&format=json&type=documents&target=answer&raw=true&token='+token+'&vdm='+vastDevMod), // upload url

			allow : '*.(<?php echo implode('|', $formats); ?>)', // allow uploads

			loadstart: function() {
				jQuery(".success-answer-documents-8768").remove();
				bar.css("width", "0%").text("0%");
				progressbar.removeClass("uk-hidden");
			},

			progress: function(percent) {
				percent = Math.ceil(percent);
				bar.css("width", percent+"%").text(percent+"%");
			},

			allcomplete: function(response) {
				bar.css("width", "100%").text("100%");
				response = JSON.parse(response);
				setTimeout(function(){
					progressbar.addClass("uk-hidden");
				}, 250);
				if (response.error){
					alert(response.error);
				} else if (response.success) {
					// set the new file name and if another is found delete it
					setFilekey(response.success, response.fileformat, 'answer', 'documents');
				}
			}
		};

		var select = UIkit2.uploadSelect($("#uikittwo-upload-select-answer-documents"), settings),
		drop   = UIkit2.uploadDrop($("#uikittwo-upload-drop-answer-documents"), settings);
	});
	<?php else: ?>
	// load uikit 3 uploader script
	jQuery(function($){
		// prep the placeholder uploading divs
		$('#uikitthree-answer-documents-uploader').show();
		$('#uikittwo-answer-documents-uploader').remove();
		$('#error-answer-documents-uploader').remove();
		$('#answer-documents-formats').html('<b><?php echo implode(', ', $formats); ?></b>');
		// get progressbar
		var bar = document.getElementById('uikitthree-progressbar-answer-documents');
		UIkit.upload('#uikitthree-upload-answer-documents', {

	 		url: JRouter('index.php?option=com_questionsanswers&task=ajax.uploadfile&format=json&type=documents&target=answer&raw=true&token='+token+'&vdm='+vastDevMod), // upload url
			multiple: true,
			allow : '*.(<?php echo implode('|', $formats); ?>)', // allow uploads

	 		beforeSend: function (environment) {
				// console.log('beforeSend', arguments);

				// The environment object can still be modified here. 
				// var {data, method, headers, xhr, responseType} = environment;
			},
			beforeAll: function () {
	       			// console.log('beforeAll', arguments);
			},
			load: function () {
				// console.log('load', arguments);
			},
			error: function () {
				// console.log('error', arguments);
			},
			complete: function () {
				// console.log('complete', arguments);
			},

			loadStart: function (e) {
				jQuery(".success-answer-documents-8768").remove();

				bar.removeAttribute('hidden');
				bar.max = e.total;
				bar.value = e.loaded;
			},

			progress: function (e) {
				bar.max = e.total;
				bar.value = e.loaded;
			},

			loadEnd: function (e) {
				bar.max = e.total;
				bar.value = e.loaded;
			},

			completeAll: function (response) {
				setTimeout(function () {
	 				bar.setAttribute('hidden', 'hidden');
				}, 250);
				// act upon the response
				if (response.response) {
					response = JSON.parse(response.response);
					if (response.error){
						alert(response.error);
					} else if (response.success) {
						// set the new file name and if another is found delete it
						setFilekey(response.success, response.fileformat, 'answer', 'documents');
					}
				}
	  		}

		});
	});
	<?php endif; ?>
<?php else: ?>
	jQuery('#error-answer-documents-uploader').html('<b><?php echo JText::_('COM_QUESTIONSANSWERS_ALLOWED_DOCUMENT_FORMATS_ARE_NOT_SET_IN_THE_GLOBAL_SETTINGS_PLEASE_NOTIFY_YOUR_SYSTEM_ADMINISTRATOR'); ?></b>');
<?php endif; ?>

<?php
	$app = JFactory::getApplication();
?>
function JRouter(link) {
<?php
	if ($app->isSite())
	{
		echo 'var url = "'.JURI::root().'";';
	}
	else
	{
		echo 'var url = "";';
	}
?>
	return url+link;
} 

function getFile(filename, fileFormat, target, type){
	// set uikit version
	var uiVer = <?php echo (int) $this->params->get('uikit_version', 2); ?>;
	// set the link
	var link = '<?php echo QuestionsanswersHelper::getFolderPath('url'); ?>';
	// build the return
	if (type === 'image') {
		var thePath = link+filename+'.'+fileFormat;
		var thedelete = '<button onclick="removeFileCheck(\''+filename+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_QUESTIONSANSWERS_REMOVE'); ?> '+target+' '+type+'</button></div>';
		return '<img alt="'+target+' Image" src="'+thePath+'" /><br /><br />'+thedelete;
	} else if (type === 'images') {
		var imageNum = filename.length;
		if (imageNum == 1) {
			var gridClass = ' uk-grid-width-1-1';
			var perRow = 1;
		} else if (imageNum == 2) {
			var gridClass = ' uk-grid-width-1-2';
			var perRow = 2;
		} else {
			var gridClass = ' uk-grid-width-1-3';
			var perRow = 3;
		}
		var counter = 1;
		var imagesBox = '<div class="uk-grid'+gridClass+'">';
		jQuery.each(filename, function(i, item) {
			imagesBox += '<div class="uk-panel">';
			var fileFormat = item.split('_')[2];
			var thePath = link+item+'.'+fileFormat;
			var thedelete = '<button onclick="removeFileCheck(\''+item+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_QUESTIONSANSWERS_REMOVE'); ?> '+target+' '+type+'</button>';
			imagesBox += '<img alt="'+target+' Image" src="'+thePath+'" /><br /><br />'+thedelete; 
			if (perRow == counter) {
				counter = 0;
				if (imageNum == perRow) {
					imagesBox += '</div>';
				} else {
					imagesBox += '</div></div><div class="uk-grid'+gridClass+'">';
				}
			} else {
				imagesBox += '</div>';
			}
			counter++;
		});
		return imagesBox + '</div></div></div>';
	} else if (type === 'documents' || type === 'media') {
		var fileNum = filename.length;
		if (fileNum == 1) {
			var gridClass = ' uk-grid-width-1-1';
			var perRow = 1;
		} else if (fileNum == 2) {
			var gridClass = ' uk-grid-width-1-2';
			var perRow = 2;
		} else {
			var gridClass = ' uk-grid-width-1-3';
			var perRow = 3;
		}
		var counter = 1;
		var fileBox = '<div class="uk-grid'+gridClass+'">';
		jQuery.each(filename, function(i, item) {
			fileBox += '<div class="uk-panel">';
			var fileFormat = item.split('_')[2];
			// set the file name
			var fileName = item.split('VDM')[1]+'.'+fileFormat;
			// set the placeholder
			var theplaceholder = '<div class="uk-width-1-1"><div class="uk-panel uk-panel-box"><center><code>[DOCLINK='+fileName+']</code> <?php echo JText::_('COM_QUESTIONSANSWERS_OR'); ?> <code>[DOCBUTTON='+fileName+']</code><br /><?php echo JText::_('COM_QUESTIONSANSWERS_ADD_ONE_OF_THESE_PLACEHOLDERS_IN_TEXT_FOR_CUSTOM_DOWNLOAD_PLACEMENT'); ?>.</center></div></div>';
			// get the download link if set
			var thedownload = '';
			if (documentsLinks.hasOwnProperty(item)) {
				thedownload = '<a href="'+JRouter(documentsLinks[item])+'" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-success"><i class="uk-icon-download"></i> <?php echo JText::_('COM_QUESTIONSANSWERS_DOWNLOAD'); ?> '+fileName+'</a>';
			}
			var thedelete = '<button onclick="removeFileCheck(\''+item+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_QUESTIONSANSWERS_REMOVE'); ?> '+fileName+'</button>';
			fileBox += theplaceholder+thedownload+thedelete; 
			if (perRow == counter) {
				counter = 0;
				if (fileNum == perRow) {
					fileBox += '</div>';
				} else {
					fileBox += '</div></div><div class="uk-grid'+gridClass+'">';
				}
			} else {
				fileBox += '</div>';
			}
			counter++;
		});
		return fileBox + '</div></div></div>';
	} else if (type === 'document') {
		var fileFormat = filename.split('_')[2];
		// set the file name
		var fileName = filename.split('VDM')[1]+'.'+fileFormat;
		// set the placeholder
		var theplaceholder = '<div class="uk-width-1-1"><div class="uk-panel uk-panel-box"><center><code>[DOCLINK='+fileName+']</code> <?php echo JText::_('COM_QUESTIONSANSWERS_OR'); ?> <code>[DOCBUTTON='+fileName+']</code><br /><?php echo JText::_('COM_QUESTIONSANSWERS_ADD_ONE_OF_THESE_PLACEHOLDERS_IN_TEXT_FOR_CUSTOM_DOWNLOAD_PLACEMENT'); ?>.</center></div></div>';
		// get the download link if set
		var thedownload = '';
		if (documentsLinks.hasOwnProperty(filename)) {
			thedownload = '<a href="'+JRouter(documentsLinks[filename])+'" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-success"><i class="uk-icon-download"></i> <?php echo JText::_('COM_QUESTIONSANSWERS_DOWNLOAD'); ?> '+fileName+'</a>';
		}
		var thedelete = '<button onclick="removeFileCheck(\''+filename+'\', \''+target+'\', \''+type+'\', \''+uiVer+'\')" type="button" class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-danger"><i class="uk-icon-trash"></i> <?php echo JText::_('COM_QUESTIONSANSWERS_REMOVE'); ?> '+fileName+'</button>';
		return theplaceholder+thedownload+thedelete + '</div>';
	}
}

</script>
