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

	@version		1.1.x
	@build			27th May, 2022
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		download.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controller');

/**
 * Questionsanswers Download Controller
 */
class QuestionsanswersControllerDownload extends JControllerLegacy
{	
	public function __construct($config)
	{
		parent::__construct($config);
		// load the tasks 
		$this->registerTask('document', 'download');
		$this->registerTask('media', 'download');
		// load params
		$this->app_params = JComponentHelper::getParams('com_questionsanswers');
		// set link options
		$this->linkoptions = $this->setLinkOptions();
	}
	
	public function download()
	{
		// get input values
		$this->input		= JFactory::getApplication()->input;
		// Check Token!
		$token 		= JSession::getFormToken();
		$call_token	= $this->input->get('token', 0, 'ALNUM');
		if($token === $call_token || $this->linkoptions['session'] == 0)
        {
			// get the task
			$task	= $this->getTask();
			switch($task)
			{
				case 'document':
				case 'media':
					if ($file = $this->input->get('file', NULL, 'STRING'))
					{
						// only continue if the file exists
						if ($details = $this->getFileDetails($file, $task))
						{
							// set headers
							$app = JFactory::getApplication();
							$app->setHeader('Accept-ranges', 'bytes', true);
							$app->setHeader('Connection', 'keep-alive', true);
							$app->setHeader('Content-Encoding', 'none', true);
							$app->setHeader('Content-disposition', 'attachment; filename="'.$details['filename'].'";', true);
							$app->setHeader('Content-Type', '"'.$details['type'].'"', true);
							$app->setHeader('Content-Length', (int) $details['size'], true);
							$app->setHeader('Content-Size', (int) $details['size'], true);
							$app->setHeader('Content-security-policy', 'referrer no-referrer', true);
							$app->setHeader('Content-Name', '"'.$details['name'].'"', true);
							$app->setHeader('Content-Version', '1.0', true);
							$app->setHeader('Content-Vendor', '"'.$details['vendor'].'"', true);
							$app->setHeader('Content-URL', '"'.JUri::getInstance().'"', true);
							$app->setHeader('cache-control', 'max-age=0', true);
							$app->setHeader('x-robots-tag', 'noindex, nofollow, noimageindex', true);
							$app->setHeader('x-content-security-policy', 'referrer no-referrer', true);
							$app->setHeader('x-webkit-csp', 'referrer no-referrer', true);
							$app->setHeader('x-content-security-policy', 'referrer no-referrer', true);
							// get the file
							readfile($details['link']); 
							$app->sendHeaders();
							$app->close();
							jexit();
						}
					}
				break;
			}
			jexit('Download error, please try again later! If the problem continues contact your system administrator.');
		}
		jexit(JText::_('JINVALID_TOKEN'));
	}
	
	/**
	 * @return array of link options
	 */
	protected function setLinkOptions()
	{
		$linkoptions = $this->app_params->get('link_option', null);
		// set the options to array
		$options = array('lock' => 0, 'session' => 0);
		if (QuestionsanswersHelper::checkArray($linkoptions))
		{
			if (in_array(1, $linkoptions))
			{
				// unlock the filename
				$options['lock'] = 1;
			}
			if (in_array(2, $linkoptions))
			{
				// check session of the links
				$options['session'] = 1;
			}
		}
		return $options;
	}
	
	protected function getFileDetails($lockedString, $type)
	{
		// check if we have a filename
		if (QuestionsanswersHelper::checkString($lockedString))
		{
			// Get the basic encryption.
			$basickey = QuestionsanswersHelper::getCryptKey('basic');
			// check if encryption is available
			if ($this->linkoptions['lock'] && $basickey)
			{
				// Get the encryption object.
				$basic = new FOFEncryptAes($basickey, 128);
				// get file name
				$filename = rtrim($basic->decryptString(QuestionsanswersHelper::base64_urldecode($lockedString)), "\0");
			}
			else
			{
				// get file name
				$filename = QuestionsanswersHelper::base64_urldecode($lockedString, true);
			}
			// check if we still have the correct filename convention to work with
			if (QuestionsanswersHelper::checkString($filename) && strpos($filename, '_') !== false && strpos($filename, 'VDM') !== false)
			{
				// get the file path
				$filePath = QuestionsanswersHelper::getFolderPath('path', 'hiddenfilepath');
				// get file formate from file name
				$filenameArray = (array) explode('_', $filename);
				// get name from file name
				$nameArray = (array) explode('VDM', $filename);
				// now load the format
				if (count($filenameArray) > 2)
				{
					// set the format
					$format = (string) $filenameArray[2];
				}
				else
				{
					return false;
				}
				// now load the name
				if (count($nameArray) > 1)
				{
					// set the name
					$name = (string) $nameArray[1];
				}
				else
				{
					return false;
				}
				
				
				// start details array
				$details = array();
				// load the link
				$details['link'] = $filePath.$filename.'.'.$format;
				// check if file exists
				if (file_exists($details['link']))
				{
					// get Site name
					$config = JFactory::getConfig();
					$details['vendor'] = $config->get('sitename');
					// set the size
					$details['size'] = filesize($details['link']);					
					// set the file name
					$details['filename'] = $name.'.'.$format;
					// set the content name
					$details['name'] = $name;
					// set the file type
					$details['type'] = QuestionsanswersHelper::mimeType($details['link']);
					// count the downloaded
					$this->countDownload($name.'.'.$format);
					// return set details
					return $details;
				}
			}
		}
		return false;
	}
	
	// Only counts the download if the helper class exist
	protected function countDownload($name)
	{
		if (method_exists('QuestionsanswersHelper','countLocalDownload'))
		{
			// count the download of the local item
			QuestionsanswersHelper::countLocalDownload($name, $this->input);
		}
	}
}
