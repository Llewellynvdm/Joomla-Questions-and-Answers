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
	@build			5th May, 2018
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		ajax.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	Questions &amp; Answers 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

/**
 * Questionsanswers Ajax Model
 */
class QuestionsanswersModelAjax extends JModelList
{
	protected $app_params;

	public function __construct()
	{
		parent::__construct();
		// get params
		$this->app_params = JComponentHelper::getParams('com_questionsanswers');

	}

	// Used in question_and_answer

	protected $viewid = array();

	protected function getViewID($call = 'table')
	{
		if (!isset($this->viewid[$call]))
		{
			// get the vdm key
			$jinput = JFactory::getApplication()->input;
			$vdm = $jinput->get('vdm', null, 'WORD');
			if ($vdm) 
			{
				if ($view = QuestionsanswersHelper::get($vdm))
				{
					$current = (array) explode('__', $view);
					if (QuestionsanswersHelper::checkString($current[0]) && isset($current[1]) && is_numeric($current[1]))
					{
						// get the view name & id
						$this->viewid[$call] = array(
							'a_id' => (int) $current[1],
							'a_view' => $current[0]
						);
					}
				}
			}
		}
		if (isset($this->viewid[$call]))
		{
			return $this->viewid[$call];
		}
		return false;
	}

	// allowed views
	protected $allowedViews = array('question_and_answer');

	// allowed targets
	protected $targets = array('main', 'answer'); 

	// allowed types
	protected $types = array('image' => 'image', 'images' => 'image', 'document' => 'document', 'documents' => 'document');

	// set some buckets
	protected $target;
	protected $targetType;
	protected $formatType;

	// set some defaults
	protected $formats = 
		array( 
			'image_formats' => array(
				1 => 'jpg',
				2 => 'jpeg',
				3 => 'gif',
				4 => 'png'),
			'document_formats' => array(
				1 => 'doc',
				2 => 'docx',
				3 => 'odt',
				4 => 'pdf',
				5 => 'csv',
				6 => 'xls',
				7 => 'xlsx',
				8 => 'ods',
				9 => 'ppt',
				10 => 'pptx',
				11 => 'pps',
				12 => 'ppsx',
				13 => 'odp',
				14 => 'zip'),
			'media_formats' => array(
				1 => 'mp3',
				2 => 'm4a',
				3 => 'ogg',
				4 => 'wav',
				5 => 'mp4',
				6 => 'm4v',
				7 => 'mov',
				8 => 'wmv',
				9 => 'avi',
				10 => 'mpg',
				11 => 'ogv',
				12 => '3gp',
				13 => '3g2'));

	// file details
	protected $fileName;
	protected $folderPath;
	protected $fullPath;
	protected $fileFormat;
	// return error if upload fails
	protected $errorMessage;
	// set uploading values
	protected $use_streams = false;
	protected $allow_unsafe = false;
	protected $safeFileOptions = array();

	public function uploadfile($target, $type)
	{
		// get the view values
		$view = $this->getViewID();
		if (in_array($target, $this->targets) && isset($this->types[$type]) && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			$this->target = (string) $target;
			$this->targetType = (string) $type;
			$this->formatType = (string) $this->types[$type];
			if ($package = $this->_getPackageFromUpload())
			{
				// now we move the file into place
				return $this->uploadNow($package, $view);
			}
			return array('error' => $this->errorMessage);
		}
		return array('error' => JText::_('COM_QUESTIONSANSWERS_THERE_HAS_BEEN_AN_ERROR'));
	}

	protected function uploadNow($package, $view)
	{
		// set the package name to file name if found
		$name = $this->formatType;
		if (isset($package['packagename']))
		{
			$name = QuestionsanswersHelper::safeString(str_replace('.'.$this->fileFormat, '', $package['packagename']), 'filename', '_', false);
		}
		$this->fileName = $this->target.'_'.$this->targetType.'_'.$this->fileFormat.'_'.QuestionsanswersHelper::randomkey(20).'VDM'.$name;
		// set the folder path
		if ($this->formatType === 'document' || $this->formatType === 'media')
		{
			// get the folder path
			$this->folderPath = QuestionsanswersHelper::getFolderPath('path', 'hiddenfilepath');
		}
		else
		{
			// get the file path
			$this->folderPath = QuestionsanswersHelper::getFolderPath();
		}
		// set full path to the file
		$this->fullPath = $this->folderPath . $this->fileName . '.' . $this->fileFormat;
		// move to target folder
		if (JFile::move($package['dir'], $this->fullPath))
		{
			// do crop/resize if it is an image and cropping is set
			if ($this->formatType === 'image')
			{
				QuestionsanswersHelper::resizeImage($this->fileName, $this->fileFormat, $this->target, $this->folderPath, $this->fullPath);
			}
			// Get the basic encription.
			$basickey = QuestionsanswersHelper::getCryptKey('basic');
			$basic = null;
			// set link options
			$linkOptions = QuestionsanswersHelper::getLinkOptions();
			// set link options
			if ($basickey)
			{
				// Get the encryption object.
				$basic = new FOFEncryptAes($basickey, 128);
			}
			// when it is documents we need to give file name in base64
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				// store the name
				$keyName = $this->fileName;
				if (QuestionsanswersHelper::checkObject($basic))
				{
					// Get the encryption object.
					$localFile = QuestionsanswersHelper::base64_urlencode($basic->encryptString($keyName));
				}
				else
				{
					// can not get the encryption object so only base64 encode
					$localFile = QuestionsanswersHelper::base64_urlencode($keyName, true);
				}
			}
			// check if we must update the current item
			if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']))
			{
				$object = new stdClass();
				$object->id = (int) $view['a_id'];
				if ($this->targetType === 'image' || $this->targetType === 'document')
				{
					if ($linkOptions['lock'] && QuestionsanswersHelper::checkObject($basic))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $basic->encryptString($this->fileName);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $this->fileName;
					}
				}
				elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
				{
					$this->fileName = $this->setFileNameArray('add', $basic, $view);
					if ($linkOptions['lock'] && QuestionsanswersHelper::checkObject($basic))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $basic->encryptString($this->fileName);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $this->fileName;
					}
					
				}
				JFactory::getDbo()->updateObject('#__questionsanswers_'.$view['a_view'], $object, 'id');
			}
			elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
			{
				$this->fileName = array($this->fileName);
				$this->fileName =  '["'.implode('", "', $this->fileName).'"]';
			}
			// set the results
			$result = array('success' =>  $this->fileName, 'fileformat' => $this->fileFormat);
			// add some more values if document format type
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				$tokenLink = '';
				if ($linkOptions['lock'] == 0)
				{
					$localFile = QuestionsanswersHelper::base64_urlencode($keyName, true);
				}
				if ($linkOptions['session'])
				{
					$tokenLink = '&token=' . JSession::getFormToken();
				}
				// if document
				if ($this->formatType === 'document')
				{
					$result['link'] = 'index.php?option=com_questionsanswers&task=download.document&file=' . $localFile . $tokenLink;
				}
				// if media
				elseif ($this->formatType === 'media')
				{
					$result['link'] = 'index.php?option=com_questionsanswers&task=download.media&file=' . $localFile . $tokenLink;
				}
				$result['key'] = $keyName;
			}
			return $result;
		}
		$this->remove($package['packagename']);
		return array('error' =>  JText::_('COM_QUESTIONSANSWERS_THERE_HAS_BEEN_AN_ERROR'));
	}

	public function removeFile($oldFile, $target, $clearDB, $type)
	{
		// get view values
		$view = $this->getViewID();
		if (in_array($target, $this->targets) && isset($this->types[$type]) && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			$this->target = (string) $target;
			$this->targetType = (string) $type;
			$this->formatType = (string) $this->types[$type];
			$this->fileName = (string) $oldFile;
			if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']))
			{
				// get user to see if he has permission to upload
				$user = JFactory::getUser();
				if (!$user->authorise($view['a_view'].'.edit.'.$this->target.'_'.$this->targetType, 'com_questionsanswers'))
				{
					return array('error' =>  JText::_('COM_QUESTIONSANSWERS_YOU_DO_NOT_HAVE_PERMISSION_TO_REMOVE_THIS_FILE'));
				}
			}
			if ($this->formatType === 'document' || $this->formatType === 'media')
			{
				// get the file path
				$this->folderPath = QuestionsanswersHelper::getFolderPath('path', 'hiddenfilepath');
			}
			else
			{
				// get the file path
				$this->folderPath = QuestionsanswersHelper::getFolderPath();
			}
			// remove from the db if there is an id
			if ($clearDB == 1 && isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
			{
				$object = new stdClass();
				$object->id = (int) $view['a_id'];
				if ($this->targetType === 'image' || $this->targetType === 'document')
				{
					$object->{$this->target.'_'.$this->targetType} = '';
					JFactory::getDbo()->updateObject('#__questionsanswers_'.$view['a_view'], $object, 'id');
				}
				elseif ($this->targetType === 'images' || $this->targetType === 'documents' || $this->targetType === 'media')
				{
					// Get the basic encription.
					$basickey = QuestionsanswersHelper::getCryptKey('basic');
					$basic = null;
					// set link options
					$linkOptions = QuestionsanswersHelper::getLinkOptions();
					if ($linkOptions['lock'] && $basickey)
					{
						// Get the encryption object.
						$basic = new FOFEncryptAes($basickey, 128);
					}
					$fileNameArray = $this->setFileNameArray('remove', $basic, $view);
					if ($linkOptions['lock'] && QuestionsanswersHelper::checkObject($basic))
					{
						// Get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $basic->encryptString($fileNameArray);
					}
					else
					{
						// can not get the encryption object.
						$object->{$this->target.'_'.$this->targetType} = $fileNameArray;
					}
					JFactory::getDbo()->updateObject('#__questionsanswers_'.$view['a_view'], $object, 'id');
				}
			}
			// load the file class
			jimport('joomla.filesystem.file');
			// remove file with this filename
			$fileFormats = $this->formats[$this->formatType .'_formats'];
			foreach ($fileFormats as $fileFormat)
			{
				if (JFile::exists($this->folderPath . $this->fileName . '.' . $fileFormat))
				{
					// remove the file
					return JFile::delete($this->folderPath . $this->fileName . '.' . $fileFormat);
				}
			}
		}
		return array('error' => JText::_('COM_QUESTIONSANSWERS_THERE_HAS_BEEN_AN_ERROR'));
	}

	protected function setFileNameArray($action, $basic, $view)
	{
		$curentFiles = QuestionsanswersHelper::getVar($view['a_view'], $view['a_id'], 'id', $this->target.'_'.$this->targetType);
		// unlock if needed
		if ($basic && $curentFiles === base64_encode(base64_decode($curentFiles, true)))
		{
			// basic decrypt data banner_image.
			$curentFiles = rtrim($basic->decryptString($curentFiles), "\0");
		}
		// convert to array if needed
		if (QuestionsanswersHelper::checkJson($curentFiles))
		{
			$curentFiles = json_decode($curentFiles, true);
		}
		// remove or add the file name
		if (QuestionsanswersHelper::checkArray($curentFiles))
		{
			if ('add' === $action)
			{
				$curentFiles[] = $this->fileName;
			}
			else
			{
				if(($key = array_search($this->fileName, $curentFiles)) !== false)
				{
					unset($curentFiles[$key]);
				}
			}
		}
		elseif ('add' === $action)
		{
			$curentFiles = array($this->fileName);
		}
		else
		{
			$curentFiles = '';
		}
		// convert to json
		if (QuestionsanswersHelper::checkArray($curentFiles))
		{
			return '["'.implode('", "', $curentFiles).'"]';
		}
		return '';
	}

	/**
	 * Works out an importation file from a HTTP upload
	 *
	 * @return file definition or false on failure
	 */
	protected function _getPackageFromUpload()
	{		
		// Get the uploaded file information
		$app	= JFactory::getApplication();
		$input	= $app->input;

		// See JInputFiles::get.
		$userfiles = $input->files->get('files', null, 'array');
		
		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$this->errorMessage = JText::_('COM_QUESTIONSANSWERS_WARNING_IMPORT_FILE_ERROR');
			return false;
		}

		// get the files from array
		$userfile = null;
		if (is_array($userfiles))
		{
			$userfile = array_values($userfiles)[0]; 
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			$this->errorMessage = JText::_('COM_QUESTIONSANSWERS_NO_IMPORT_FILE_SELECTED');
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			$this->errorMessage = JText::_('COM_QUESTIONSANSWERS_WARNING_IMPORT_UPLOAD_ERROR');
			return false;
		}

		// Build the appropriate paths
		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$p_file = JFile::upload($tmp_src, $tmp_dest, $this->use_streams, $this->allow_unsafe, $this->safeFileOptions);

		// Was the package downloaded?
		if (!$p_file)
		{
			$session = JFactory::getSession();
			$session->clear('package');
			$session->clear('dataType');
			$session->clear('hasPackage');
			$this->errorMessage = JText::_('COM_QUESTIONSANSWERS_COULD_NOT_UPLOAD_THE_FILE');
			// was not uploaded
			return false;
		}

		// check that this is a valid file
		$package = $this->check($userfile['name']);

		return $package;
	}
	
	/**
	 * Check a file and verifies it as a allowed file format file
	 *
	 * @param   string  $archivename  The uploaded package filename or import directory
	 *
	 * @return  array  of elements
	 *
	 */
	protected function check($archivename)
	{
		// Clean the name
		$archivename = JPath::clean($archivename);
		// get file format
		$this->fileFormat = strtolower(pathinfo($archivename, PATHINFO_EXTENSION));
		// get fileFormat key
		$allowedFormats = array();
		if (in_array($this->fileFormat, $this->formats[$this->formatType .'_formats']))
		{
			// get allowed formats
			$allowedFormats = (array) $this->app_params->get($this->formatType.'_formats', null);
		}
		// check the extension
		if (!in_array($this->fileFormat, $allowedFormats))
		{
			// Cleanup the import files
			$this->remove($archivename);
			$this->errorMessage = JText::_('COM_QUESTIONSANSWERS_DOES_NOT_HAVE_A_VALID_FILE_TYPE');
			return false;
		}

		// check permission if user
		$view = $this->getViewID();
		if (isset($view['a_id']) && $view['a_id'] > 0 && isset($view['a_view']) && in_array($view['a_view'], $this->allowedViews))
		{
			// get user to see if he has permission to upload
			$user = JFactory::getUser();
			if (!$user->authorise($view['a_view'].'.edit.'.$this->target.'_'.$this->targetType, 'com_questionsanswers'))
			{
				// Cleanup the import files
				$this->remove($archivename);
				$this->errorMessage = JText::_('COM_QUESTIONSANSWERS_YOU_DO_NOT_HAVE_PERMISSION_TO_UPLOAD_AN'.$this->targetType);
				return false;
			}
		}
		
		$config			= JFactory::getConfig();
		// set Package Name
		$check['packagename']	= $archivename;
		
		// set directory
		$check['dir']		= $config->get('tmp_path'). '/' .$archivename;
		
		return $check;
	}
	
	/**
	 * Clean up temporary uploaded file
	 *
	 * @param   string  $package    Name of the uploaded file
	 *
	 * @return  boolean  True on success
	 *
	 */
	protected function remove($package)
	{
		jimport('joomla.filesystem.file');
		
		$config		= JFactory::getConfig();
		$package	= $config->get('tmp_path'). '/' .$package;

		// Is the package file a valid file?
		if (is_file($package))
		{
			JFile::delete($package);
		}
		elseif (is_file(JPath::clean($package)))
		{
			// It might also be just a base filename
			JFile::delete(JPath::clean($package));
		}
	}
 

	// Used in questions_and_answers
	/**
	* Get Rows of Group data
	* 
	* @return    string    Formatted html table row
	*/
	public function getColumns(&$page)
	{
		// return columns
		if ('downloads' == $page)
		{
			return array(
				array( 'name' => 'download', 'title' => '<center>'.JText::_('COM_QUESTIONSANSWERS_FILE_DOWNLOAD').'</center>', 'type' => 'HTML', 'sorted' => true, 'direction' => 'ASC'),
				array( 'name' => 'category_name', 'title' => JText::_('COM_QUESTIONSANSWERS_CATEGORY'), 'type' => 'html', 'sort-use' => 'text', 'breakpoints' => 'sm')
			);
		}
		else
		{
			return array(
				array( 'name' => 'question', 'title' => '<center>'.JText::_('COM_QUESTIONSANSWERS_QUESTIONS').'</center>', 'type' => 'text', 'sorted' => true, 'direction' => 'ASC'),
				array( 'name' => 'answer', 'title' => JText::_('COM_QUESTIONSANSWERS_ANSWER'), 'type' => 'html', 'breakpoints' => 'all'),
				array( 'name' => 'category_name', 'title' => JText::_('COM_QUESTIONSANSWERS_CATEGORY'), 'type' => 'html', 'sort-use' => 'text', 'breakpoints' => 'all')
			);
		}
	}

	/**
	* Get Rows of Group data
	* 
	* @return    string    Formatted html table row
	*/
	public function getRows(&$key,&$page)
	{
		$values = QuestionsanswersHelper::get($key, null);
		// check if this is valid json
		if (QuestionsanswersHelper::checkJson($values))
		{
			$array = json_decode($values, true);
			// now check that array is all numbers, and set to int
			if (QuestionsanswersHelper::checkArray($array))
			{
				$this->idArray = $array;
			}
			// at last lets get started
			if (QuestionsanswersHelper::checkArray($this->idArray))
			{
				$items = $this->getItems();
				if ($items)
				{
					$user = JFactory::getUser();
					$this->filelink = QuestionsanswersHelper::getFolderPath('url');
					$this->linkoptions = QuestionsanswersHelper::getLinkOptions();
					// start row builder
					$rows = array();
					foreach($items as $nr => $item)
					{
						// load the question
						if ('downloads' != $page && QuestionsanswersHelper::checkString($item->question))
						{
							// build the row
							$rows[$nr] = array();
							$rows[$nr]['question']['value'] = $item->question . $this->setEditButton($item, $user);
							$rows[$nr]['question']['options']  = array('sortValue' => QuestionsanswersHelper::safeString($item->question, 'Ww'));
							// load the question answer
							$rows[$nr]['answer']['value'] = $this->setAnswer($item);
							$rows[$nr]['answer']['options']  = array('sortValue' => QuestionsanswersHelper::safeString($item->answer));
							// load the category name
							$rows[$nr]['category_name']['value'] = '<span>'.$item->category_name.'</span>';
							$rows[$nr]['category_name']['options']  = array('filterValue' => $item->category_name, 'sortValue' => $item->category_name);
						}
						// load the downloads
						elseif ('downloads' == $page && $download = $this->setDownloads($item))
						{
							// load the category downloads
							$rows[$nr]['download']['value'] = $download['buttons'] . $this->setEditButton($item, $user);
							$rows[$nr]['download']['options']  = array('sortValue' => $download['names']);
							// load the category name
							$rows[$nr]['category_name']['value'] = '<span>'.$item->category_name.'</span>';
							$rows[$nr]['category_name']['options']  = array('filterValue' => $item->category_name, 'sortValue' => $item->category_name);
						}
								
					}
				}
			}
			if (QuestionsanswersHelper::checkArray($rows))
			{
				// just return this for now :)
				return $rows;
			}
		}
		return false;
	}

	/**
	 * @return string
	 */
	protected function setDownloads($item)
	{
		// get downloads
		$downloads = $this->getDownloads($item->answer_documents);
		// load the downloads
		$bucket = array();
		if (QuestionsanswersHelper::checkArray($downloads['checker']))
		{
			$buttons = array();
			foreach ($downloads['checker'] as $key)
			{
				if (isset($downloads['doc']['[DOCBUTTON'.$key]))
				{
					$buttons[] = $downloads['doc']['[DOCBUTTON'.$key];
				}
			}
			// if we have a button
			if (QuestionsanswersHelper::checkArray($buttons))
			{
				$bucket['buttons'] = '<p data-uk-margin>' . implode("\n", $buttons) . '</p>';
			}
		}
		// if we have download buttons
		if (QuestionsanswersHelper::checkArray($bucket))
		{
			$bucket['names'] = implode(" ", $downloads['fileName']);
			return $bucket;
		}
		return false;
	}

	/**
	 * @return string
	 */
	protected function setAnswer($item)
	{
		$answer = array();
		if (QuestionsanswersHelper::checkString($item->main_image))
		{
			// set image link
			if (strpos($item->main_image, '_') !== false)
			{
				$extention = explode('_', $item->main_image);
				$actualName = 'question image';
				if (strpos($item->main_image, 'VDM') !== false)
				{
					$fileNameArray = explode('VDM', $item->main_image);
					if (isset($fileNameArray[1]) && QuestionsanswersHelper::checkString($fileNameArray[1]))
					{
						$actualName = $fileNameArray[1];
					}
				}
				if (isset($extention[2]))
				{
					$answer[] = '<img class="uk-margin" src="'.$this->filelink.$item->main_image.'.'.$extention[2].'" alt="'. $actualName .'"/>';
				}
			}
		}
		// get downloads
		$downloads = $this->getDownloads($item->answer_documents);
		if (QuestionsanswersHelper::checkString($item->answer))
		{
			if (isset($downloads['checker']) && QuestionsanswersHelper::checkArray($downloads['checker']))
			{
				// first check what links are in answer text
				foreach ($downloads['checker'] as $nr => $checking)
				{
					if (stripos($item->answer, $checking) !== false)
					{
						unset($downloads['checker'][$nr]);
					}
				}
				$item->answer = str_replace(array_keys($downloads['link']), array_values($downloads['link']), $item->answer);
				$answer[] = str_replace(array_keys($downloads['doc']), array_values($downloads['doc']), $item->answer);
			}
			else
			{
				$answer[] = $item->answer;
			}
		}
		// load the downloads that remain
		if (QuestionsanswersHelper::checkArray($downloads['checker']))
		{
			$buttons = array();
			foreach ($downloads['checker'] as $key)
			{
				if (isset($downloads['doc']['[DOCBUTTON'.$key]))
				{
					$buttons[] = $downloads['doc']['[DOCBUTTON'.$key];
				}
			}
			$answer[] = '<p data-uk-margin>' . implode("\n", $buttons) . '</p>';
		}
		// if we have an answer return it
		if (QuestionsanswersHelper::checkArray($answer))
		{
			return '<div>' . implode("", $answer) . '</div>';
		}
		return '<div><p>'.JText::_('COM_QUESTIONSANSWERS_NO_ANSWER_WHERE_FOUND_PLEASE_CHECK_AGAIN_LATTER').'</p></div>';
	}

	/**
	 * @return array of links
	 */
	protected function getDownloads($values)
	{
		if (isset($values))
		{
			// first make sure it is Json values
			if (QuestionsanswersHelper::checkJson($values))
			{
				$values = json_decode($values, true);
			}
			// now check if it is an array
			if (QuestionsanswersHelper::checkArray($values))
			{
				$tokenLink = ''; 
				if ($this->linkoptions['session'])
				{
					$tokenLink =  '&token=' . JSession::getFormToken();;
				}
				$domain = JURI::root();
				$downloads = array();
				$downloads['doc'] = array();
				$downloads['link'] = array();
				$downloads['checker'] = array();
				$downloads['fileName'] = array();
				foreach ($values as $fileName)
				{
					// make sure this a correct file name
					if (strpos($fileName, '_') !== false && strpos($fileName, 'VDM') !== false)
					{
						$fileArray = explode('_', $fileName);
						$nameArray = explode('VDM', $fileName);
						// more checks
						if (isset($nameArray[1]) && isset($fileArray[2]))
						{
							if ($this->linkoptions['lock'] && isset($this->basickey) && $this->basickey)
							{
								// Get the encryption object.
								$localFile = QuestionsanswersHelper::base64_urlencode($this->locker->encryptString($fileName));
							}
							else
							{
								// can not get the encryption object so only base64 encode
								$localFile = QuestionsanswersHelper::base64_urlencode($fileName, true);
							}
							// build link 
							$link = $domain . 'index.php?option=com_questionsanswers&task=download.document&file=' . $localFile . $tokenLink;
							// build the file name
							$_fileName = $nameArray[1] . '.' . $fileArray[2];
							$downloads['link']['[DOCLINK='.$_fileName.']'] = '<a href="' . $link . '" tytle=" ' . JText::_('COM_QUESTIONSANSWERS_DOWNLOAD') . '">' . $_fileName . '</a>';
							$downloads['doc']['[DOCBUTTON='.$_fileName.']'] = '<a href="' . $link . '" class="uk-button uk-button-success uk-margin-small-bottom"><i class="uk-icon-download"></i> ' . JText::_('COM_QUESTIONSANSWERS_DOWNLOAD') . ' ' . $_fileName . '</a>';
							$downloads['checker'][] = '='.$_fileName.']';
							$downloads['fileName'][] = $nameArray[1];
						} 
					}
				}
				if (QuestionsanswersHelper::checkArray($downloads['link']))
				{
					return $downloads;
				}
			}
		}
		return false;
	}

	protected function setEditButton(&$item, &$user)
	{
		$edit = "index.php?option=com_questionsanswers&view=questions_and_answers&task=question_and_answer.edit&ref=questions_and_answers";
		$canCheckin = $item->checked_out == $user->id || $item->checked_out == 0;
		$canEdit = QuestionsanswersHelper::hasEditAccess($item->id, $user);
		if ($canEdit && !$item->checked_out)
		{
			$button = ' <a href="'.$edit.'&id='.$item->id.'">'
					. '<i class="uk-icon-pencil"></i></a>';
		}
		elseif ($canEdit && $canCheckin)
		{
			$button = ' <a href="'.$edit.'&id='.$item->id.'">'
					. '<i class="uk-icon-lock"></i></a>'; 
		}
		elseif ($canEdit)
		{
			$button = ' <a href="#" disabled>'
					. '<i class="uk-icon-lock"></i></a>'; 
		}
		else
		{
			$button = '';
		}
		return $button;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$this->user		= JFactory::getUser();
		$this->levels		= $this->user->getAuthorisedViewLevels();
		// Make sure all records load, since no pagination allowed.
		$this->setState('list.limit', 0);
		// Get a db connection.
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__questionsanswers_question_and_answer as a
		$query->select($db->quoteName(
			array('a.id','a.question','a.answer','a.main_image','a.answer_documents','a.checked_out','a.catid'),
			array('id','question','answer','main_image','answer_documents','checked_out','catid')));
		$query->from($db->quoteName('#__questionsanswers_question_and_answer', 'a'));

		// Get from #__categories as c
		$query->select($db->quoteName('c.title','category_name'));
		$query->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')');

		// filter by ids
		if (QuestionsanswersHelper::checkArray($this->idArray))
		{
			$query->where('a.id IN ('. implode(',',$this->idArray).')' );
		}
		$query->where('a.published = 1');
		$query->order('a.question ASC');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// load parent items
		$items = parent::getItems();
		// Get a Crypt Key
		$this->basickey = QuestionsanswersHelper::getCryptKey('basic');
		$this->locker = new FOFEncryptAes($this->basickey, 128);
		// make sure we have items to work with
		if (isset($items) && QuestionsanswersHelper::checkArray($items))
		{
			$locked = array('main_image','answer_documents');
			foreach ($items as $nr => &$item)
			{
				foreach ($locked as $value)
				{
					// open the locked values
					if (isset($item->$value))
					{
						// use the local decryption option
						$item->$value = $this->decryptString($item->$value);
					}
					else
					{
						$item->$value = '';
					}
				}
			}
		} 
		// return items
		return $items;
	}

	protected function decryptString($value)
	{
		if ($this->basickey && !is_numeric($value) && $value === base64_encode(base64_decode($value, true)))
		{
			// decrypt  value
			$value= rtrim($this->locker->decryptString($value), "\0");
		}
		return $value;
	}
}
