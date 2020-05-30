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
	@build			30th May, 2020
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		questionsanswers.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Language;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Questionsanswers component helper
 */
abstract class QuestionsanswersHelper
{
	/**
	 * Composer Switch
	 * 
	 * @var      array
	 */
	protected static $composer = array();

	/**
	 * The Main Active Language
	 * 
	 * @var      string
	 */
	public static $langTag;

	/**
	*	The Global Site Event Method.
	**/
	public static function globalEvent($document)
	{
		// the Session keeps track of all data related to the current session of this user
		self::loadSession();
	}

	/**
	* 	the globals
	**/
	protected static $params;
	protected static $user;
	protected static $locker;
	protected static $basickey;
 
	/**
	* the Butler
	**/
	public static $session = array();

	/**
	* the Butler Assistant 
	**/
	protected static $localSession = array();

	/**
	* start a session if not already set, and load with data
	**/
	public static function loadSession()
	{
		if (!isset(self::$session) || !self::checkObject(self::$session))
		{
			self::$session = JFactory::getSession();
		}
		// set the defaults
		self::setSessionDefaults();
	}

	/**
	* give Session more to keep
	**/
	public static function set($key, $value)
	{
		if (!isset(self::$session) || !self::checkObject(self::$session))
		{
			self::$session = JFactory::getSession();
		}
		// set to local memory to speed up program
		self::$localSession[$key] = $value;
		// load to session for later use
		return self::$session->set($key, self::$localSession[$key]);
	}

	/**
	* get info from Session
	**/
	public static function get($key, $default = null)
	{
		if (!isset(self::$session) || !self::checkObject(self::$session))
		{
			self::$session = JFactory::getSession();
		}
		// check if in local memory
		if (!isset(self::$localSession[$key]))
		{
			// set to local memory to speed up program
			self::$localSession[$key] = self::$session->get($key, $default);
		}
		return self::$localSession[$key];
	}

	/**
	* 	set the session defaults if not set
	**/
	protected static function setSessionDefaults()
	{
		// noting set for now
	}

	/**
	 * @param $fileName
	 * @param $fileFormat
	 * @param $target
	 * @param $path
	 * @param $fullPath
	 * @return bool
	 */
	public static function resizeImage($fileName, $fileFormat, $target, $path, $fullPath)
	{
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_questionsanswers');
		}
		// first check if we should resize this target
		if (1 == self::$params->get('crop_'.$target, 0))
		{
			// load the size to be set
			$height = self::$params->get($target.'_height', 'not_set');
			$width = self::$params->get($target.'_width', 'not_set');
			// get image properties
			$image = self::getImageFileProperties($fileName.'.'.$fileFormat, $path);
			// make sure we have an object
			if (self::checkObject($image))
			{
				if ($width !== 'not_set' && $height !== 'not_set' && ($image->width != $width || $image->height != $height))
				{
					// if image is huge and should only be scaled, resize it on the fly
					if(($image->width > 900 || $image->height > 700) && ($height == 0 || $width == 0))
					{
						if($fileFormat == "jpg" || $fileFormat == "jpeg" )
						{
							$src = imagecreatefromjpeg($fullPath);
						}
						elseif($fileFormat == "png")
						{
							$src = imagecreatefrompng($fullPath);
						}
						elseif($fileFormat == "gif")
						{
							$src = imagecreatefromgif($fullPath);
						}
						else
						{
							return false;
						}
						if ($height != 0)
						{
							$hRatio = $image->height / $height;
						}
						if ($width != 0)
						{
							$wRatio = $image->width / $width;
						}
						if (isset($hRatio) && isset($wRatio))
						{
							$maxRatio	= max($wRatio, $hRatio);
						}
						elseif (isset($wRatio))
						{
							$maxRatio	= $wRatio;
						}
						elseif (isset($hRatio))
						{
							$maxRatio	= $hRatio;
						}
						if ($maxRatio > 1)
						{
							$newwidth	= $image->width / $maxRatio;
							$newheight	= $image->height / $maxRatio;
						}
						else
						{
							$newwidth	= $image->width;
							$newheight	= $image->height;
						}

						$tmp			= imagecreatetruecolor($newwidth, $newheight);
						$backgroundColor	= imagecolorallocate($tmp, 255, 255, 255);

						imagefill($tmp, 0, 0, $backgroundColor);
						imagecopyresampled($tmp, $src, 0, 0, 0, 0,$newwidth, $newheight, $image->width, $image->height);
						imagejpeg($tmp, $fullPath, 100);
						imagedestroy($src);
						imagedestroy($tmp);
					}
					// only continue if image should be cropped
					if ($height != 0 && $width != 0)
					{
						// Include wideimage - http://wideimage.sourceforge.net
						require_once(JPATH_ADMINISTRATOR . '/components/com_questionsanswers/helpers/wideimage/WideImage.php');
						$builder = WideImage::load($fullPath);
						$resized = $builder->resize($width, $height, 'outside')->crop('center', 'middle', $width, $height);
						$resized->saveToFile($fullPath);
					}
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $image
	 * @return bool|stdClass
	 */
	public static function getImageFileProperties($image, $folder = false)
	{
		if ($folder)
		{
			$localfolder = $folder;
		}
		else
		{
			$setimagesfolder = JComponentHelper::getParams('com_questionsanswers')->get('setimagesfolder', 1);
			if (2 == $setimagesfolder)
			{
				$localfolder = JComponentHelper::getParams('com_questionsanswers')->get('imagesfolder', JPATH_SITE.'/images/questionsanswers');
			}
			elseif (1 == $setimagesfolder)
			{
				$localfolder =  JPATH_SITE.'/images';
			}
			else // just in-case :)
			{
				$localfolder =  JPATH_SITE.'/images/questionsanswers';
			}
		}
		// import all needed classes
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.image.image');
		// setup the folder if it does not exist
		if (JFolder::exists($localfolder) && JFile::exists($localfolder.'/'.$image))
		{
			$properties = JImage::getImageFileProperties($localfolder.'/'.$image);
			// check if we have properties
			if (self::checkObject($properties))
			{
				// remove the server path
				$imagePath = trim(str_replace(JPATH_SITE,'',$localfolder),'/').'/'.$image;
				// now add the src path to show the image
				$properties->src = JURI::root().$imagePath;
				// return the image properties
				return $properties;
			}
		}
		return false;
	}


	/**
	* Get the file path or url
	* 
	* @param  string   $type              The (url/path) type to return
	* @param  string   $target            The Params Target name (if set)
	* @param  string   $default           The default path if not set in Params (fallback path)
	* @param  bool     $createIfNotSet    The switch to create the folder if not found
	*
	* @return  string    On success the path or url is returned based on the type requested
	* 
	*/
	public static function getFolderPath($type = 'path', $target = 'folderpath', $default = '', $createIfNotSet = true)
	{
		// make sure to always have a string/path
		if(!self::checkString($default))
		{
			$default = JPATH_SITE . '/images/';
		}
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_questionsanswers');
		}
		$folderPath = self::$params->get($target, $default);
		jimport('joomla.filesystem.folder');
		// create the folder if it does not exist
		if ($createIfNotSet && !JFolder::exists($folderPath))
		{
			JFolder::create($folderPath);
		}
		// return the url
		if ('url' === $type)
		{
			if (strpos($folderPath, JPATH_SITE) !== false)
			{
				$folderPath = trim( str_replace( JPATH_SITE, '', $folderPath), '/');
				return JURI::root() . $folderPath . '/';
			}
			// since the path is behind the root folder of the site, return only the root url (may be used to build the link)
			return JURI::root();
		}
		// sanitize the path
		return '/' . trim( $folderPath, '/' ) . '/';
	}


	/**
	* Get the file path or url
	*
	* @param  string   $type              The (url/path) type to return
	* @param  string   $target            The Params Target name (if set)
	* @param  string   $fileType          The kind of filename to generate (if not set no file name is generated)
	* @param  string   $key               The key to adjust the filename (if not set ignored)
	* @param  string   $default           The default path if not set in Params (fallback path)
	* @param  bool     $createIfNotSet    The switch to create the folder if not found
	*
	* @return  string    On success the path or url is returned based on the type requested
	*
	*/
	public static function getFilePath($type = 'path', $target = 'filepath', $fileType = null, $key = '', $default = '', $createIfNotSet = true)
	{
		// make sure to always have a string/path
		if(!self::checkString($default))
		{
			$default = JPATH_SITE . '/images/';
		}
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_questionsanswers');
		}
		$filePath = self::$params->get($target, $default);
		// check the file path (revert to default only of not a hidden file path)
		if ('hiddenfilepath' !== $target && strpos($filePath, JPATH_SITE) === false)
		{
			$filePath = $default;
		}
		jimport('joomla.filesystem.folder');
		// create the folder if it does not exist
		if ($createIfNotSet && !JFolder::exists($filePath))
		{
			JFolder::create($filePath);
		}
		// setup the file name
		$fileName = '';
		// Get basic key
		$basickey = 'Th!s_iS_n0t_sAfe_buT_b3tter_then_n0thiug';
		if (method_exists(get_called_class(), "getCryptKey")) 
		{
			$basickey = self::getCryptKey('basic', $basickey);
		}
		// check the key
		if (!self::checkString($key))
		{
			$key = 'vDm';
		}
		// set the file name
		if (self::checkString($fileType))
		{
			// set the name
			$fileName = trim(md5($type.$target.$basickey.$key) . '.' . trim($fileType, '.'));
		}
		else
		{
			$fileName = trim(md5($type.$target.$basickey.$key)) . '.txt';
		}
		// return the url
		if ('url' === $type)
		{
			if (strpos($filePath, JPATH_SITE) !== false)
			{
				$filePath = trim( str_replace( JPATH_SITE, '', $filePath), '/');
				return JURI::root() . $filePath . '/' . $fileName;
			}
			// since the path is behind the root folder of the site, return only the root url (may be used to build the link)
			return JURI::root();
		}
		// sanitize the path
		return '/' . trim( $filePath, '/' ) . '/' . $fileName;
	}


	/**
	 * @param $ids
	 * @param $type
	 * @return array|null
	 */
	public static function getNames($ids, $type)
	{
		// setup the get array
		$get = array('a.id');
		switch($type)
		{
			case 'catid':
				// set related values
				$get[] = 'a.title';
				$table = '#__categories';
			break;		
		}
		if (!isset($table) || !self::checkArray($ids))
		{
			return null;
		}
		// check the array of ids
		if (self::checkArray($ids))
		{
			foreach ($ids as $id)
			{ 
				if (!(is_numeric($id)))
				{
					return null;
				} 
			}
		}
		// now load all custom values
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName($get));
		$query->from($db->quoteName($table, 'a'));
		$query->where('a.id IN (' . implode(', ', $ids) . ')');
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			$items = $db->loadObjectList();
			$bucket = array();
			foreach ($items as $item)
			{
				switch($type)
				{
					case 'catid':
						// set related values
						$bucket[$item->id] = $item->title;
					break;
				}
			}
			return $bucket;
		}
		return null;
	}

	/**
	 * Change to nice fancy date
	 */
	public static function fancyDate($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('jS \o\f F Y',$date);
	}

	/**
	 * get date based in period past
	 */
	public static function fancyDynamicDate($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		// older then year
		$lastyear = date("Y", strtotime("-1 year"));
		$tragetyear = date("Y", $date);
		if ($tragetyear <= $lastyear)
		{
			return date('m/d/y', $date);
		}
		// same day
		$yesterday = strtotime("-1 day");
		if ($date > $yesterday)
		{
			return date('g:i A', $date);
		}
		// just month day
		return date('M j', $date);
	}

	/**
	 * Change to nice fancy day time and date
	 */
	public static function fancyDayTimeDate($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('D ga jS \o\f F Y',$time);
	}

	/**
	 * Change to nice fancy time and date
	 */
	public static function fancyDateTime($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('(G:i) jS \o\f F Y',$time);
	}

	/**
	 * Change to nice hour:minutes time
	 */
	public static function fancyTime($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('G:i',$time);
	}

	/**
	 * set the date day as Sunday through Saturday
	 */
	public static function setDayName($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('l', $date);
	}

	/**
	 * set the date month as January through December
	 */
	public static function setMonthName($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('F', $date);
	}

	/**
	 * set the date day as 1st
	 */
	public static function setDay($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('jS', $date);
	}

	/**
	 * set the date month as 5
	 */
	public static function setMonth($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('n', $date);
	}

	/**
	 * set the date year as 2004 (for charts)
	 */
	public static function setYear($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('Y', $date);
	}

	/**
	 * set the date as 2004/05 (for charts)
	 */
	public static function setYearMonth($date, $spacer = '/')
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('Y' . $spacer . 'm', $date);
	}

	/**
	 * set the date as 2004/05/03 (for charts)
	 */
	public static function setYearMonthDay($date, $spacer = '/')
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('Y' . $spacer . 'm' . $spacer . 'd', $date);
	}

	/**
	 * Check if string is a valid time stamp
	 */
	public static function isValidTimeStamp($timestamp)
	{
		return ((int) $timestamp === $timestamp)
		&& ($timestamp <= PHP_INT_MAX)
		&& ($timestamp >= ~PHP_INT_MAX);
	}


	/**
	* 	prepare base64 string for url
	**/
	public static function base64_urlencode($string, $encode = false)
	{
		if ($encode)
		{
			$string = base64_encode($string);
		}
		return str_replace(array('+', '/'), array('-', '_'), $string);
	}

	/**
	* 	prepare base64 string form url
	**/
	public static function base64_urldecode($string, $decode = false)
	{
		$string = str_replace(array('-', '_'), array('+', '/'), $string);
		if ($decode)
		{
			$string = base64_decode($string);
		}
		return $string;
	}


	/**
	 * The Dynamic Data Array
	 *
	 * @var     array
	 */
	protected static $dynamicData = array();

	/**
	 * Set the Dynamic Data
	 *
	 * @param   string   $data             The data to update
	 * @param   array   $placeholders      The placeholders to use to update data
	 *
	 * @return string   of updated data
	 *
	 */
	public static function setDynamicData($data, $placeholders)
	{
		// make sure data is a string & placeholders is an array
		if (self::checkString($data) && self::checkArray($placeholders))
		{
			// store in memory in case it is build multiple times
			$keyMD5 = md5($data.json_encode($placeholders));
			if (!isset(self::$dynamicData[$keyMD5]))
			{
				// remove all values that are not strings (just to be safe)
				$placeholders = array_filter($placeholders, function ($val){ if (self::checkArray($val) || self::checkObject($val)) { return false; } return true; });
				// model (basic) based on logic
				self::setTheIF($data, $placeholders);
				// update the string and store in memory
				self::$dynamicData[$keyMD5] = str_replace(array_keys($placeholders), array_values($placeholders), $data);
			}
			// return updated string
			return self::$dynamicData[$keyMD5];
		}
		return $data;
	}

	/**
	 * Set the IF statements
	 *
	 * @param   string   $string           The string to update
	 * @param   array   $placeholders      The placeholders to use to update string
	 *
	 * @return void
	 *
	 */
	protected static function setTheIF(&$string, $placeholders)
	{		
		// only normal if endif
		$condition 	= '[a-z0-9\_\-]+';
		$inner		= '((?:(?!\[\/?IF)(?!\[\/?ELSE)(?!\[\/?ELSEIF).)*?)';
		$if		= '\[IF\s?('.$condition.')\]';
		$elseif		= '\[ELSEIF\s?('.$condition.')\]';
		$else		= '\[ELSE\]';
		$endif		= '\[ENDIF\]';
		// set the patterns
		$patterns = array();
		// normal if endif
		$patterns[] = '#'.$if.$inner.$endif.'#is';
		// normal if else endif
		$patterns[] = '#'.$if.$inner.$else.$inner.$endif.'#is';
		// dynamic if elseif's endif
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		// dynamic if elseif's else endif
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		// run the patterns to setup the string
		foreach ($patterns as $pattern)
		{
			while (preg_match($pattern, $string, $match))
			{
				$keep 	= self::remainderIF($match, $placeholders);
				$string	= preg_replace($pattern, $keep, $string, 1);
			}
		}
	}

	/**
	 * Set the remainder IF
	 *
	 * @param   array   $match            The match search
	 * @param   array   $placeholders     The placeholders to use to match
	 *
	 * @return string of remainder
	 *
	 */
	protected static function remainderIF(&$match, &$placeholders)
	{	
		// default we keep nothing
		$keep = '';
		$found = false;
		// get match lenght
		$length = count($match);
		// ranges to check
		$ii = range(2,30,2); // even numbers (content)
		$iii = range(1, 25, 2); // odd numbers (placeholder)
		// if empty value remove whole line else show line but remove all [CODE]
		foreach ($iii as $content => $placeholder)
		{
			if (isset($match[$placeholder]) && empty($placeholders['['.$match[$placeholder].']']))
			{
				// keep nothing or next option
				$keep = '';
			}
			elseif (isset($match[$ii[$content]]))
			{
				$keep = addcslashes($match[$ii[$content]], '$');
				$found = true;
				break;
			}
		}
		// if not found load else if set
		if (!$found && in_array($length, $ii))
		{
			$keep = addcslashes($match[$length - 1], '$');
		}
		return $keep;
	}

	public static function hasEditAccess($recordId, $userId = null, $to = 'question_and_answer')
	{
		if (self::checkObject($userId) && $userId instanceof JUser)
		{
			$user = $userId;
		}
		elseif (is_numeric($userId) && $userId > 0)
		{
			$user = JFactory::getUser($userId);
		}
		else
		{
			$user = JFactory::getUser();
		}
		if ($user->authorise($to.'.edit', 'com_questionsanswers.'.$to.'.' . (int)  $recordId))
		{
			return true;
		}
		return false;
	}

	/**
	 * @return array of link options
	 */
	public static function getLinkOptions($lock = 0, $session = 0, $params = null)
	{
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			if (self::checkObject($params))
			{
				self::$params = $params;
			}
			else
			{
				self::$params = JComponentHelper::getParams('com_questionsanswers');
			}
		}
		$linkoptions = self::$params->get('link_option', null);
		// set the options to array
		$options = array('lock' => $lock, 'session' => $session);
		if (QuestionsanswersHelper::checkArray($linkoptions))
		{
			if (in_array(1, $linkoptions))
			{
				// lock the filename
				$options['lock'] = 1;
			}
			if (in_array(2, $linkoptions))
			{
				// add session to the links
				$options['session'] = 1;
			}
		}
		return $options;
	}

	/**
	 * File Extension to Mimetype
	 * https://gist.github.com/Llewellynvdm/74be373357e131b8775a7582c3de508b
	 * http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
	 * 
	 * @var     array
	 **/
	protected static $fileExtensionToMimeType = array(
		'123'			=> 'application/vnd.lotus-1-2-3',
		'3dml'			=> 'text/vnd.in3d.3dml',
		'3ds'			=> 'image/x-3ds',
		'3g2'			=> 'video/3gpp2',
		'3gp'			=> 'video/3gpp',
		'7z'			=> 'application/x-7z-compressed',
		'aab'			=> 'application/x-authorware-bin',
		'aac'			=> 'audio/x-aac',
		'aam'			=> 'application/x-authorware-map',
		'aas'			=> 'application/x-authorware-seg',
		'abw'			=> 'application/x-abiword',
		'ac'			=> 'application/pkix-attr-cert',
		'acc'			=> 'application/vnd.americandynamics.acc',
		'ace'			=> 'application/x-ace-compressed',
		'acu'			=> 'application/vnd.acucobol',
		'acutc'			=> 'application/vnd.acucorp',
		'adp'			=> 'audio/adpcm',
		'aep'			=> 'application/vnd.audiograph',
		'afm'			=> 'application/x-font-type1',
		'afp'			=> 'application/vnd.ibm.modcap',
		'ahead'			=> 'application/vnd.ahead.space',
		'ai'			=> 'application/postscript',
		'aif'			=> 'audio/x-aiff',
		'aifc'			=> 'audio/x-aiff',
		'aiff'			=> 'audio/x-aiff',
		'air'			=> 'application/vnd.adobe.air-application-installer-package+zip',
		'ait'			=> 'application/vnd.dvb.ait',
		'ami'			=> 'application/vnd.amiga.ami',
		'apk'			=> 'application/vnd.android.package-archive',
		'appcache'		=> 'text/cache-manifest',
		'application'	=> 'application/x-ms-application',
		'apr'			=> 'application/vnd.lotus-approach',
		'arc'			=> 'application/x-freearc',
		'asc'			=> 'application/pgp-signature',
		'asf'			=> 'video/x-ms-asf',
		'asm'			=> 'text/x-asm',
		'aso'			=> 'application/vnd.accpac.simply.aso',
		'asx'			=> 'video/x-ms-asf',
		'atc'			=> 'application/vnd.acucorp',
		'atom'			=> 'application/atom+xml',
		'atomcat'		=> 'application/atomcat+xml',
		'atomsvc'		=> 'application/atomsvc+xml',
		'atx'			=> 'application/vnd.antix.game-component',
		'au'			=> 'audio/basic',
		'avi'			=> 'video/x-msvideo',
		'aw'			=> 'application/applixware',
		'azf'			=> 'application/vnd.airzip.filesecure.azf',
		'azs'			=> 'application/vnd.airzip.filesecure.azs',
		'azw'			=> 'application/vnd.amazon.ebook',
		'bat'			=> 'application/x-msdownload',
		'bcpio'			=> 'application/x-bcpio',
		'bdf'			=> 'application/x-font-bdf',
		'bdm'			=> 'application/vnd.syncml.dm+wbxml',
		'bed'			=> 'application/vnd.realvnc.bed',
		'bh2'			=> 'application/vnd.fujitsu.oasysprs',
		'bin'			=> 'application/octet-stream',
		'blb'			=> 'application/x-blorb',
		'blorb'			=> 'application/x-blorb',
		'bmi'			=> 'application/vnd.bmi',
		'bmp'			=> 'image/bmp',
		'book'			=> 'application/vnd.framemaker',
		'box'			=> 'application/vnd.previewsystems.box',
		'boz'			=> 'application/x-bzip2',
		'bpk'			=> 'application/octet-stream',
		'btif'			=> 'image/prs.btif',
		'bz'			=> 'application/x-bzip',
		'bz2'			=> 'application/x-bzip2',
		'c'				=> 'text/x-c',
		'c11amc'		=> 'application/vnd.cluetrust.cartomobile-config',
		'c11amz'		=> 'application/vnd.cluetrust.cartomobile-config-pkg',
		'c4d'			=> 'application/vnd.clonk.c4group',
		'c4f'			=> 'application/vnd.clonk.c4group',
		'c4g'			=> 'application/vnd.clonk.c4group',
		'c4p'			=> 'application/vnd.clonk.c4group',
		'c4u'			=> 'application/vnd.clonk.c4group',
		'cab'			=> 'application/vnd.ms-cab-compressed',
		'caf'			=> 'audio/x-caf',
		'cap'			=> 'application/vnd.tcpdump.pcap',
		'car'			=> 'application/vnd.curl.car',
		'cat'			=> 'application/vnd.ms-pki.seccat',
		'cb7'			=> 'application/x-cbr',
		'cba'			=> 'application/x-cbr',
		'cbr'			=> 'application/x-cbr',
		'cbt'			=> 'application/x-cbr',
		'cbz'			=> 'application/x-cbr',
		'cc'			=> 'text/x-c',
		'cct'			=> 'application/x-director',
		'ccxml'			=> 'application/ccxml+xml',
		'cdbcmsg'		=> 'application/vnd.contact.cmsg',
		'cdf'			=> 'application/x-netcdf',
		'cdkey'			=> 'application/vnd.mediastation.cdkey',
		'cdmia'			=> 'application/cdmi-capability',
		'cdmic'			=> 'application/cdmi-container',
		'cdmid'			=> 'application/cdmi-domain',
		'cdmio'			=> 'application/cdmi-object',
		'cdmiq'			=> 'application/cdmi-queue',
		'cdx'			=> 'chemical/x-cdx',
		'cdxml'			=> 'application/vnd.chemdraw+xml',
		'cdy'			=> 'application/vnd.cinderella',
		'cer'			=> 'application/pkix-cert',
		'cfs'			=> 'application/x-cfs-compressed',
		'cgm'			=> 'image/cgm',
		'chat'			=> 'application/x-chat',
		'chm'			=> 'application/vnd.ms-htmlhelp',
		'chrt'			=> 'application/vnd.kde.kchart',
		'cif'			=> 'chemical/x-cif',
		'cii'			=> 'application/vnd.anser-web-certificate-issue-initiation',
		'cil'			=> 'application/vnd.ms-artgalry',
		'cla'			=> 'application/vnd.claymore',
		'class'			=> 'application/java-vm',
		'clkk'			=> 'application/vnd.crick.clicker.keyboard',
		'clkp'			=> 'application/vnd.crick.clicker.palette',
		'clkt'			=> 'application/vnd.crick.clicker.template',
		'clkw'			=> 'application/vnd.crick.clicker.wordbank',
		'clkx'			=> 'application/vnd.crick.clicker',
		'clp'			=> 'application/x-msclip',
		'cmc'			=> 'application/vnd.cosmocaller',
		'cmdf'			=> 'chemical/x-cmdf',
		'cml'			=> 'chemical/x-cml',
		'cmp'			=> 'application/vnd.yellowriver-custom-menu',
		'cmx'			=> 'image/x-cmx',
		'cod'			=> 'application/vnd.rim.cod',
		'com'			=> 'application/x-msdownload',
		'conf'			=> 'text/plain',
		'cpio'			=> 'application/x-cpio',
		'cpp'			=> 'text/x-c',
		'cpt'			=> 'application/mac-compactpro',
		'crd'			=> 'application/x-mscardfile',
		'crl'			=> 'application/pkix-crl',
		'crt'			=> 'application/x-x509-ca-cert',
		'cryptonote'	=> 'application/vnd.rig.cryptonote',
		'csh'			=> 'application/x-csh',
		'csml'			=> 'chemical/x-csml',
		'csp'			=> 'application/vnd.commonspace',
		'css'			=> 'text/css',
		'cst'			=> 'application/x-director',
		'csv'			=> 'text/csv',
		'cu'			=> 'application/cu-seeme',
		'curl'			=> 'text/vnd.curl',
		'cww'			=> 'application/prs.cww',
		'cxt'			=> 'application/x-director',
		'cxx'			=> 'text/x-c',
		'dae'			=> 'model/vnd.collada+xml',
		'daf'			=> 'application/vnd.mobius.daf',
		'dart'			=> 'application/vnd.dart',
		'dataless'		=> 'application/vnd.fdsn.seed',
		'davmount'		=> 'application/davmount+xml',
		'dbk'			=> 'application/docbook+xml',
		'dcr'			=> 'application/x-director',
		'dcurl'			=> 'text/vnd.curl.dcurl',
		'dd2'			=> 'application/vnd.oma.dd2+xml',
		'ddd'			=> 'application/vnd.fujixerox.ddd',
		'deb'			=> 'application/x-debian-package',
		'def'			=> 'text/plain',
		'deploy'		=> 'application/octet-stream',
		'der'			=> 'application/x-x509-ca-cert',
		'dfac'			=> 'application/vnd.dreamfactory',
		'dgc'			=> 'application/x-dgc-compressed',
		'dic'			=> 'text/x-c',
		'dir'			=> 'application/x-director',
		'dis'			=> 'application/vnd.mobius.dis',
		'dist'			=> 'application/octet-stream',
		'distz'			=> 'application/octet-stream',
		'djv'			=> 'image/vnd.djvu',
		'djvu'			=> 'image/vnd.djvu',
		'dll'			=> 'application/x-msdownload',
		'dmg'			=> 'application/x-apple-diskimage',
		'dmp'			=> 'application/vnd.tcpdump.pcap',
		'dms'			=> 'application/octet-stream',
		'dna'			=> 'application/vnd.dna',
		'doc'			=> 'application/msword',
		'docm'			=> 'application/vnd.ms-word.document.macroenabled.12',
		'docx'			=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dot'			=> 'application/msword',
		'dotm'			=> 'application/vnd.ms-word.template.macroenabled.12',
		'dotx'			=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'dp'			=> 'application/vnd.osgi.dp',
		'dpg'			=> 'application/vnd.dpgraph',
		'dra'			=> 'audio/vnd.dra',
		'dsc'			=> 'text/prs.lines.tag',
		'dssc'			=> 'application/dssc+der',
		'dtb'			=> 'application/x-dtbook+xml',
		'dtd'			=> 'application/xml-dtd',
		'dts'			=> 'audio/vnd.dts',
		'dtshd'			=> 'audio/vnd.dts.hd',
		'dump'			=> 'application/octet-stream',
		'dvb'			=> 'video/vnd.dvb.file',
		'dvi'			=> 'application/x-dvi',
		'dwf'			=> 'model/vnd.dwf',
		'dwg'			=> 'image/vnd.dwg',
		'dxf'			=> 'image/vnd.dxf',
		'dxp'			=> 'application/vnd.spotfire.dxp',
		'dxr'			=> 'application/x-director',
		'ecelp4800'		=> 'audio/vnd.nuera.ecelp4800',
		'ecelp7470'		=> 'audio/vnd.nuera.ecelp7470',
		'ecelp9600'		=> 'audio/vnd.nuera.ecelp9600',
		'ecma'			=> 'application/ecmascript',
		'edm'			=> 'application/vnd.novadigm.edm',
		'edx'			=> 'application/vnd.novadigm.edx',
		'efif'			=> 'application/vnd.picsel',
		'ei6'			=> 'application/vnd.pg.osasli',
		'elc'			=> 'application/octet-stream',
		'emf'			=> 'application/x-msmetafile',
		'eml'			=> 'message/rfc822',
		'emma'			=> 'application/emma+xml',
		'emz'			=> 'application/x-msmetafile',
		'eol'			=> 'audio/vnd.digital-winds',
		'eot'			=> 'application/vnd.ms-fontobject',
		'eps'			=> 'application/postscript',
		'epub'			=> 'application/epub+zip',
		'es3'			=> 'application/vnd.eszigno3+xml',
		'esa'			=> 'application/vnd.osgi.subsystem',
		'esf'			=> 'application/vnd.epson.esf',
		'et3'			=> 'application/vnd.eszigno3+xml',
		'etx'			=> 'text/x-setext',
		'eva'			=> 'application/x-eva',
		'evy'			=> 'application/x-envoy',
		'exe'			=> 'application/x-msdownload',
		'exi'			=> 'application/exi',
		'ext'			=> 'application/vnd.novadigm.ext',
		'ez'			=> 'application/andrew-inset',
		'ez2'			=> 'application/vnd.ezpix-album',
		'ez3'			=> 'application/vnd.ezpix-package',
		'f'				=> 'text/x-fortran',
		'f4v'			=> 'video/x-f4v',
		'f77'			=> 'text/x-fortran',
		'f90'			=> 'text/x-fortran',
		'fbs'			=> 'image/vnd.fastbidsheet',
		'fcdt'			=> 'application/vnd.adobe.formscentral.fcdt',
		'fcs'			=> 'application/vnd.isac.fcs',
		'fdf'			=> 'application/vnd.fdf',
		'fe_launch'		=> 'application/vnd.denovo.fcselayout-link',
		'fg5'			=> 'application/vnd.fujitsu.oasysgp',
		'fgd'			=> 'application/x-director',
		'fh'			=> 'image/x-freehand',
		'fh4'			=> 'image/x-freehand',
		'fh5'			=> 'image/x-freehand',
		'fh7'			=> 'image/x-freehand',
		'fhc'			=> 'image/x-freehand',
		'fig'			=> 'application/x-xfig',
		'flac'			=> 'audio/x-flac',
		'fli'			=> 'video/x-fli',
		'flo'			=> 'application/vnd.micrografx.flo',
		'flv'			=> 'video/x-flv',
		'flw'			=> 'application/vnd.kde.kivio',
		'flx'			=> 'text/vnd.fmi.flexstor',
		'fly'			=> 'text/vnd.fly',
		'fm'			=> 'application/vnd.framemaker',
		'fnc'			=> 'application/vnd.frogans.fnc',
		'for'			=> 'text/x-fortran',
		'fpx'			=> 'image/vnd.fpx',
		'frame'			=> 'application/vnd.framemaker',
		'fsc'			=> 'application/vnd.fsc.weblaunch',
		'fst'			=> 'image/vnd.fst',
		'ftc'			=> 'application/vnd.fluxtime.clip',
		'fti'			=> 'application/vnd.anser-web-funds-transfer-initiation',
		'fvt'			=> 'video/vnd.fvt',
		'fxp'			=> 'application/vnd.adobe.fxp',
		'fxpl'			=> 'application/vnd.adobe.fxp',
		'fzs'			=> 'application/vnd.fuzzysheet',
		'g2w'			=> 'application/vnd.geoplan',
		'g3'			=> 'image/g3fax',
		'g3w'			=> 'application/vnd.geospace',
		'gac'			=> 'application/vnd.groove-account',
		'gam'			=> 'application/x-tads',
		'gbr'			=> 'application/rpki-ghostbusters',
		'gca'			=> 'application/x-gca-compressed',
		'gdl'			=> 'model/vnd.gdl',
		'geo'			=> 'application/vnd.dynageo',
		'gex'			=> 'application/vnd.geometry-explorer',
		'ggb'			=> 'application/vnd.geogebra.file',
		'ggt'			=> 'application/vnd.geogebra.tool',
		'ghf'			=> 'application/vnd.groove-help',
		'gif'			=> 'image/gif',
		'gim'			=> 'application/vnd.groove-identity-message',
		'gml'			=> 'application/gml+xml',
		'gmx'			=> 'application/vnd.gmx',
		'gnumeric'		=> 'application/x-gnumeric',
		'gph'			=> 'application/vnd.flographit',
		'gpx'			=> 'application/gpx+xml',
		'gqf'			=> 'application/vnd.grafeq',
		'gqs'			=> 'application/vnd.grafeq',
		'gram'			=> 'application/srgs',
		'gramps'		=> 'application/x-gramps-xml',
		'gre'			=> 'application/vnd.geometry-explorer',
		'grv'			=> 'application/vnd.groove-injector',
		'grxml'			=> 'application/srgs+xml',
		'gsf'			=> 'application/x-font-ghostscript',
		'gtar'			=> 'application/x-gtar',
		'gtm'			=> 'application/vnd.groove-tool-message',
		'gtw'			=> 'model/vnd.gtw',
		'gv'			=> 'text/vnd.graphviz',
		'gxf'			=> 'application/gxf',
		'gxt'			=> 'application/vnd.geonext',
		'h'				=> 'text/x-c',
		'h261'			=> 'video/h261',
		'h263'			=> 'video/h263',
		'h264'			=> 'video/h264',
		'hal'			=> 'application/vnd.hal+xml',
		'hbci'			=> 'application/vnd.hbci',
		'hdf'			=> 'application/x-hdf',
		'hh'			=> 'text/x-c',
		'hlp'			=> 'application/winhlp',
		'hpgl'			=> 'application/vnd.hp-hpgl',
		'hpid'			=> 'application/vnd.hp-hpid',
		'hps'			=> 'application/vnd.hp-hps',
		'hqx'			=> 'application/mac-binhex40',
		'htke'			=> 'application/vnd.kenameaapp',
		'htm'			=> 'text/html',
		'html'			=> 'text/html',
		'hvd'			=> 'application/vnd.yamaha.hv-dic',
		'hvp'			=> 'application/vnd.yamaha.hv-voice',
		'hvs'			=> 'application/vnd.yamaha.hv-script',
		'i2g'			=> 'application/vnd.intergeo',
		'icc'			=> 'application/vnd.iccprofile',
		'ice'			=> 'x-conference/x-cooltalk',
		'icm'			=> 'application/vnd.iccprofile',
		'ico'			=> 'image/x-icon',
		'ics'			=> 'text/calendar',
		'ief'			=> 'image/ief',
		'ifb'			=> 'text/calendar',
		'ifm'			=> 'application/vnd.shana.informed.formdata',
		'iges'			=> 'model/iges',
		'igl'			=> 'application/vnd.igloader',
		'igm'			=> 'application/vnd.insors.igm',
		'igs'			=> 'model/iges',
		'igx'			=> 'application/vnd.micrografx.igx',
		'iif'			=> 'application/vnd.shana.informed.interchange',
		'imp'			=> 'application/vnd.accpac.simply.imp',
		'ims'			=> 'application/vnd.ms-ims',
		'in'			=> 'text/plain',
		'ink'			=> 'application/inkml+xml',
		'inkml'			=> 'application/inkml+xml',
		'install'		=> 'application/x-install-instructions',
		'iota'			=> 'application/vnd.astraea-software.iota',
		'ipfix'			=> 'application/ipfix',
		'ipk'			=> 'application/vnd.shana.informed.package',
		'irm'			=> 'application/vnd.ibm.rights-management',
		'irp'			=> 'application/vnd.irepository.package+xml',
		'iso'			=> 'application/x-iso9660-image',
		'itp'			=> 'application/vnd.shana.informed.formtemplate',
		'ivp'			=> 'application/vnd.immervision-ivp',
		'ivu'			=> 'application/vnd.immervision-ivu',
		'jad'			=> 'text/vnd.sun.j2me.app-descriptor',
		'jam'			=> 'application/vnd.jam',
		'jar'			=> 'application/java-archive',
		'java'			=> 'text/x-java-source',
		'jisp'			=> 'application/vnd.jisp',
		'jlt'			=> 'application/vnd.hp-jlyt',
		'jnlp'			=> 'application/x-java-jnlp-file',
		'joda'			=> 'application/vnd.joost.joda-archive',
		'jpe'			=> 'image/jpeg',
		'jpeg'			=> 'image/jpeg',
		'jpg'			=> 'image/jpeg',
		'jpgm'			=> 'video/jpm',
		'jpgv'			=> 'video/jpeg',
		'jpm'			=> 'video/jpm',
		'js'			=> 'application/javascript',
		'json'			=> 'application/json',
		'jsonml'		=> 'application/jsonml+json',
		'kar'			=> 'audio/midi',
		'karbon'		=> 'application/vnd.kde.karbon',
		'kfo'			=> 'application/vnd.kde.kformula',
		'kia'			=> 'application/vnd.kidspiration',
		'kml'			=> 'application/vnd.google-earth.kml+xml',
		'kmz'			=> 'application/vnd.google-earth.kmz',
		'kne'			=> 'application/vnd.kinar',
		'knp'			=> 'application/vnd.kinar',
		'kon'			=> 'application/vnd.kde.kontour',
		'kpr'			=> 'application/vnd.kde.kpresenter',
		'kpt'			=> 'application/vnd.kde.kpresenter',
		'kpxx'			=> 'application/vnd.ds-keypoint',
		'ksp'			=> 'application/vnd.kde.kspread',
		'ktr'			=> 'application/vnd.kahootz',
		'ktx'			=> 'image/ktx',
		'ktz'			=> 'application/vnd.kahootz',
		'kwd'			=> 'application/vnd.kde.kword',
		'kwt'			=> 'application/vnd.kde.kword',
		'lasxml'		=> 'application/vnd.las.las+xml',
		'latex'			=> 'application/x-latex',
		'lbd'			=> 'application/vnd.llamagraphics.life-balance.desktop',
		'lbe'			=> 'application/vnd.llamagraphics.life-balance.exchange+xml',
		'les'			=> 'application/vnd.hhe.lesson-player',
		'lha'			=> 'application/x-lzh-compressed',
		'link66'		=> 'application/vnd.route66.link66+xml',
		'list'			=> 'text/plain',
		'list3820'		=> 'application/vnd.ibm.modcap',
		'listafp'		=> 'application/vnd.ibm.modcap',
		'lnk'			=> 'application/x-ms-shortcut',
		'log'			=> 'text/plain',
		'lostxml'		=> 'application/lost+xml',
		'lrf'			=> 'application/octet-stream',
		'lrm'			=> 'application/vnd.ms-lrm',
		'ltf'			=> 'application/vnd.frogans.ltf',
		'lvp'			=> 'audio/vnd.lucent.voice',
		'lwp'			=> 'application/vnd.lotus-wordpro',
		'lzh'			=> 'application/x-lzh-compressed',
		'm13'			=> 'application/x-msmediaview',
		'm14'			=> 'application/x-msmediaview',
		'm1v'			=> 'video/mpeg',
		'm21'			=> 'application/mp21',
		'm2a'			=> 'audio/mpeg',
		'm2v'			=> 'video/mpeg',
		'm3a'			=> 'audio/mpeg',
		'm3u'			=> 'audio/x-mpegurl',
		'm3u8'			=> 'application/vnd.apple.mpegurl',
		'm4a'			=> 'audio/mp4',
		'm4u'			=> 'video/vnd.mpegurl',
		'm4v'			=> 'video/x-m4v',
		'ma'			=> 'application/mathematica',
		'mads'			=> 'application/mads+xml',
		'mag'			=> 'application/vnd.ecowin.chart',
		'maker'			=> 'application/vnd.framemaker',
		'man'			=> 'text/troff',
		'mar'			=> 'application/octet-stream',
		'mathml'		=> 'application/mathml+xml',
		'mb'			=> 'application/mathematica',
		'mbk'			=> 'application/vnd.mobius.mbk',
		'mbox'			=> 'application/mbox',
		'mc1'			=> 'application/vnd.medcalcdata',
		'mcd'			=> 'application/vnd.mcd',
		'mcurl'			=> 'text/vnd.curl.mcurl',
		'mdb'			=> 'application/x-msaccess',
		'mdi'			=> 'image/vnd.ms-modi',
		'me'			=> 'text/troff',
		'mesh'			=> 'model/mesh',
		'meta4'			=> 'application/metalink4+xml',
		'metalink'		=> 'application/metalink+xml',
		'mets'			=> 'application/mets+xml',
		'mfm'			=> 'application/vnd.mfmp',
		'mft'			=> 'application/rpki-manifest',
		'mgp'			=> 'application/vnd.osgeo.mapguide.package',
		'mgz'			=> 'application/vnd.proteus.magazine',
		'mid'			=> 'audio/midi',
		'midi'			=> 'audio/midi',
		'mie'			=> 'application/x-mie',
		'mif'			=> 'application/vnd.mif',
		'mime'			=> 'message/rfc822',
		'mj2'			=> 'video/mj2',
		'mjp2'			=> 'video/mj2',
		'mk3d'			=> 'video/x-matroska',
		'mka'			=> 'audio/x-matroska',
		'mks'			=> 'video/x-matroska',
		'mkv'			=> 'video/x-matroska',
		'mlp'			=> 'application/vnd.dolby.mlp',
		'mmd'			=> 'application/vnd.chipnuts.karaoke-mmd',
		'mmf'			=> 'application/vnd.smaf',
		'mmr'			=> 'image/vnd.fujixerox.edmics-mmr',
		'mng'			=> 'video/x-mng',
		'mny'			=> 'application/x-msmoney',
		'mobi'			=> 'application/x-mobipocket-ebook',
		'mods'			=> 'application/mods+xml',
		'mov'			=> 'video/quicktime',
		'movie'			=> 'video/x-sgi-movie',
		'mp2'			=> 'audio/mpeg',
		'mp21'			=> 'application/mp21',
		'mp2a'			=> 'audio/mpeg',
		'mp3'			=> 'audio/mpeg',
		'mp4'			=> 'video/mp4',
		'mp4a'			=> 'audio/mp4',
		'mp4s'			=> 'application/mp4',
		'mp4v'			=> 'video/mp4',
		'mpc'			=> 'application/vnd.mophun.certificate',
		'mpe'			=> 'video/mpeg',
		'mpeg'			=> 'video/mpeg',
		'mpg'			=> 'video/mpeg',
		'mpg4'			=> 'video/mp4',
		'mpga'			=> 'audio/mpeg',
		'mpkg'			=> 'application/vnd.apple.installer+xml',
		'mpm'			=> 'application/vnd.blueice.multipass',
		'mpn'			=> 'application/vnd.mophun.application',
		'mpp'			=> 'application/vnd.ms-project',
		'mpt'			=> 'application/vnd.ms-project',
		'mpy'			=> 'application/vnd.ibm.minipay',
		'mqy'			=> 'application/vnd.mobius.mqy',
		'mrc'			=> 'application/marc',
		'mrcx'			=> 'application/marcxml+xml',
		'ms'			=> 'text/troff',
		'mscml'			=> 'application/mediaservercontrol+xml',
		'mseed'			=> 'application/vnd.fdsn.mseed',
		'mseq'			=> 'application/vnd.mseq',
		'msf'			=> 'application/vnd.epson.msf',
		'msh'			=> 'model/mesh',
		'msi'			=> 'application/x-msdownload',
		'msl'			=> 'application/vnd.mobius.msl',
		'msty'			=> 'application/vnd.muvee.style',
		'mts'			=> 'model/vnd.mts',
		'mus'			=> 'application/vnd.musician',
		'musicxml'		=> 'application/vnd.recordare.musicxml+xml',
		'mvb'			=> 'application/x-msmediaview',
		'mwf'			=> 'application/vnd.mfer',
		'mxf'			=> 'application/mxf',
		'mxl'			=> 'application/vnd.recordare.musicxml',
		'mxml'			=> 'application/xv+xml',
		'mxs'			=> 'application/vnd.triscape.mxs',
		'mxu'			=> 'video/vnd.mpegurl',
		'n-gage'		=> 'application/vnd.nokia.n-gage.symbian.install',
		'n3'			=> 'text/n3',
		'nb'			=> 'application/mathematica',
		'nbp'			=> 'application/vnd.wolfram.player',
		'nc'			=> 'application/x-netcdf',
		'ncx'			=> 'application/x-dtbncx+xml',
		'nfo'			=> 'text/x-nfo',
		'ngdat'			=> 'application/vnd.nokia.n-gage.data',
		'nitf'			=> 'application/vnd.nitf',
		'nlu'			=> 'application/vnd.neurolanguage.nlu',
		'nml'			=> 'application/vnd.enliven',
		'nnd'			=> 'application/vnd.noblenet-directory',
		'nns'			=> 'application/vnd.noblenet-sealer',
		'nnw'			=> 'application/vnd.noblenet-web',
		'npx'			=> 'image/vnd.net-fpx',
		'nsc'			=> 'application/x-conference',
		'nsf'			=> 'application/vnd.lotus-notes',
		'ntf'			=> 'application/vnd.nitf',
		'nzb'			=> 'application/x-nzb',
		'oa2'			=> 'application/vnd.fujitsu.oasys2',
		'oa3'			=> 'application/vnd.fujitsu.oasys3',
		'oas'			=> 'application/vnd.fujitsu.oasys',
		'obd'			=> 'application/x-msbinder',
		'obj'			=> 'application/x-tgif',
		'oda'			=> 'application/oda',
		'odb'			=> 'application/vnd.oasis.opendocument.database',
		'odc'			=> 'application/vnd.oasis.opendocument.chart',
		'odf'			=> 'application/vnd.oasis.opendocument.formula',
		'odft'			=> 'application/vnd.oasis.opendocument.formula-template',
		'odg'			=> 'application/vnd.oasis.opendocument.graphics',
		'odi'			=> 'application/vnd.oasis.opendocument.image',
		'odm'			=> 'application/vnd.oasis.opendocument.text-master',
		'odp'			=> 'application/vnd.oasis.opendocument.presentation',
		'ods'			=> 'application/vnd.oasis.opendocument.spreadsheet',
		'odt'			=> 'application/vnd.oasis.opendocument.text',
		'oga'			=> 'audio/ogg',
		'ogg'			=> 'audio/ogg',
		'ogv'			=> 'video/ogg',
		'ogx'			=> 'application/ogg',
		'omdoc'			=> 'application/omdoc+xml',
		'onepkg'		=> 'application/onenote',
		'onetmp'		=> 'application/onenote',
		'onetoc'		=> 'application/onenote',
		'onetoc2'		=> 'application/onenote',
		'opf'			=> 'application/oebps-package+xml',
		'opml'			=> 'text/x-opml',
		'oprc'			=> 'application/vnd.palm',
		'org'			=> 'application/vnd.lotus-organizer',
		'osf'			=> 'application/vnd.yamaha.openscoreformat',
		'osfpvg'		=> 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
		'otc'			=> 'application/vnd.oasis.opendocument.chart-template',
		'otf'			=> 'font/otf',
		'otg'			=> 'application/vnd.oasis.opendocument.graphics-template',
		'oth'			=> 'application/vnd.oasis.opendocument.text-web',
		'oti'			=> 'application/vnd.oasis.opendocument.image-template',
		'otp'			=> 'application/vnd.oasis.opendocument.presentation-template',
		'ots'			=> 'application/vnd.oasis.opendocument.spreadsheet-template',
		'ott'			=> 'application/vnd.oasis.opendocument.text-template',
		'oxps'			=> 'application/oxps',
		'oxt'			=> 'application/vnd.openofficeorg.extension',
		'p'				=> 'text/x-pascal',
		'p10'			=> 'application/pkcs10',
		'p12'			=> 'application/x-pkcs12',
		'p7b'			=> 'application/x-pkcs7-certificates',
		'p7c'			=> 'application/pkcs7-mime',
		'p7m'			=> 'application/pkcs7-mime',
		'p7r'			=> 'application/x-pkcs7-certreqresp',
		'p7s'			=> 'application/pkcs7-signature',
		'p8'			=> 'application/pkcs8',
		'pas'			=> 'text/x-pascal',
		'paw'			=> 'application/vnd.pawaafile',
		'pbd'			=> 'application/vnd.powerbuilder6',
		'pbm'			=> 'image/x-portable-bitmap',
		'pcap'			=> 'application/vnd.tcpdump.pcap',
		'pcf'			=> 'application/x-font-pcf',
		'pcl'			=> 'application/vnd.hp-pcl',
		'pclxl'			=> 'application/vnd.hp-pclxl',
		'pct'			=> 'image/x-pict',
		'pcurl'			=> 'application/vnd.curl.pcurl',
		'pcx'			=> 'image/x-pcx',
		'pdb'			=> 'application/vnd.palm',
		'pdf'			=> 'application/pdf',
		'pfa'			=> 'application/x-font-type1',
		'pfb'			=> 'application/x-font-type1',
		'pfm'			=> 'application/x-font-type1',
		'pfr'			=> 'application/font-tdpfr',
		'pfx'			=> 'application/x-pkcs12',
		'pgm'			=> 'image/x-portable-graymap',
		'pgn'			=> 'application/x-chess-pgn',
		'pgp'			=> 'application/pgp-encrypted',
		'pic'			=> 'image/x-pict',
		'pkg'			=> 'application/octet-stream',
		'pki'			=> 'application/pkixcmp',
		'pkipath'		=> 'application/pkix-pkipath',
		'plb'			=> 'application/vnd.3gpp.pic-bw-large',
		'plc'			=> 'application/vnd.mobius.plc',
		'plf'			=> 'application/vnd.pocketlearn',
		'pls'			=> 'application/pls+xml',
		'pml'			=> 'application/vnd.ctc-posml',
		'png'			=> 'image/png',
		'pnm'			=> 'image/x-portable-anymap',
		'portpkg'		=> 'application/vnd.macports.portpkg',
		'pot'			=> 'application/vnd.ms-powerpoint',
		'potm'			=> 'application/vnd.ms-powerpoint.template.macroenabled.12',
		'potx'			=> 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'ppam'			=> 'application/vnd.ms-powerpoint.addin.macroenabled.12',
		'ppd'			=> 'application/vnd.cups-ppd',
		'ppm'			=> 'image/x-portable-pixmap',
		'pps'			=> 'application/vnd.ms-powerpoint',
		'ppsm'			=> 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
		'ppsx'			=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'ppt'			=> 'application/vnd.ms-powerpoint',
		'pptm'			=> 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
		'pptx'			=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'pqa'			=> 'application/vnd.palm',
		'prc'			=> 'application/x-mobipocket-ebook',
		'pre'			=> 'application/vnd.lotus-freelance',
		'prf'			=> 'application/pics-rules',
		'ps'			=> 'application/postscript',
		'psb'			=> 'application/vnd.3gpp.pic-bw-small',
		'psd'			=> 'image/vnd.adobe.photoshop',
		'psf'			=> 'application/x-font-linux-psf',
		'pskcxml'		=> 'application/pskc+xml',
		'ptid'			=> 'application/vnd.pvi.ptid1',
		'pub'			=> 'application/x-mspublisher',
		'pvb'			=> 'application/vnd.3gpp.pic-bw-var',
		'pwn'			=> 'application/vnd.3m.post-it-notes',
		'pya'			=> 'audio/vnd.ms-playready.media.pya',
		'pyv'			=> 'video/vnd.ms-playready.media.pyv',
		'qam'			=> 'application/vnd.epson.quickanime',
		'qbo'			=> 'application/vnd.intu.qbo',
		'qfx'			=> 'application/vnd.intu.qfx',
		'qps'			=> 'application/vnd.publishare-delta-tree',
		'qt'			=> 'video/quicktime',
		'qwd'			=> 'application/vnd.quark.quarkxpress',
		'qwt'			=> 'application/vnd.quark.quarkxpress',
		'qxb'			=> 'application/vnd.quark.quarkxpress',
		'qxd'			=> 'application/vnd.quark.quarkxpress',
		'qxl'			=> 'application/vnd.quark.quarkxpress',
		'qxt'			=> 'application/vnd.quark.quarkxpress',
		'ra'			=> 'audio/x-pn-realaudio',
		'ram'			=> 'audio/x-pn-realaudio',
		'rar'			=> 'application/x-rar-compressed',
		'ras'			=> 'image/x-cmu-raster',
		'rcprofile'		=> 'application/vnd.ipunplugged.rcprofile',
		'rdf'			=> 'application/rdf+xml',
		'rdz'			=> 'application/vnd.data-vision.rdz',
		'rep'			=> 'application/vnd.businessobjects',
		'res'			=> 'application/x-dtbresource+xml',
		'rgb'			=> 'image/x-rgb',
		'rif'			=> 'application/reginfo+xml',
		'rip'			=> 'audio/vnd.rip',
		'ris'			=> 'application/x-research-info-systems',
		'rl'			=> 'application/resource-lists+xml',
		'rlc'			=> 'image/vnd.fujixerox.edmics-rlc',
		'rld'			=> 'application/resource-lists-diff+xml',
		'rm'			=> 'application/vnd.rn-realmedia',
		'rmi'			=> 'audio/midi',
		'rmp'			=> 'audio/x-pn-realaudio-plugin',
		'rms'			=> 'application/vnd.jcp.javame.midlet-rms',
		'rmvb'			=> 'application/vnd.rn-realmedia-vbr',
		'rnc'			=> 'application/relax-ng-compact-syntax',
		'roa'			=> 'application/rpki-roa',
		'roff'			=> 'text/troff',
		'rp9'			=> 'application/vnd.cloanto.rp9',
		'rpss'			=> 'application/vnd.nokia.radio-presets',
		'rpst'			=> 'application/vnd.nokia.radio-preset',
		'rq'			=> 'application/sparql-query',
		'rs'			=> 'application/rls-services+xml',
		'rsd'			=> 'application/rsd+xml',
		'rss'			=> 'application/rss+xml',
		'rtf'			=> 'application/rtf',
		'rtx'			=> 'text/richtext',
		's'				=> 'text/x-asm',
		's3m'			=> 'audio/s3m',
		'saf'			=> 'application/vnd.yamaha.smaf-audio',
		'sbml'			=> 'application/sbml+xml',
		'sc'			=> 'application/vnd.ibm.secure-container',
		'scd'			=> 'application/x-msschedule',
		'scm'			=> 'application/vnd.lotus-screencam',
		'scq'			=> 'application/scvp-cv-request',
		'scs'			=> 'application/scvp-cv-response',
		'scurl'			=> 'text/vnd.curl.scurl',
		'sda'			=> 'application/vnd.stardivision.draw',
		'sdc'			=> 'application/vnd.stardivision.calc',
		'sdd'			=> 'application/vnd.stardivision.impress',
		'sdkd'			=> 'application/vnd.solent.sdkm+xml',
		'sdkm'			=> 'application/vnd.solent.sdkm+xml',
		'sdp'			=> 'application/sdp',
		'sdw'			=> 'application/vnd.stardivision.writer',
		'see'			=> 'application/vnd.seemail',
		'seed'			=> 'application/vnd.fdsn.seed',
		'sema'			=> 'application/vnd.sema',
		'semd'			=> 'application/vnd.semd',
		'semf'			=> 'application/vnd.semf',
		'ser'			=> 'application/java-serialized-object',
		'setpay'		=> 'application/set-payment-initiation',
		'setreg'		=> 'application/set-registration-initiation',
		'sfd-hdstx'		=> 'application/vnd.hydrostatix.sof-data',
		'sfs'			=> 'application/vnd.spotfire.sfs',
		'sfv'			=> 'text/x-sfv',
		'sgi'			=> 'image/sgi',
		'sgl'			=> 'application/vnd.stardivision.writer-global',
		'sgm'			=> 'text/sgml',
		'sgml'			=> 'text/sgml',
		'sh'			=> 'application/x-sh',
		'shar'			=> 'application/x-shar',
		'shf'			=> 'application/shf+xml',
		'sid'			=> 'image/x-mrsid-image',
		'sig'			=> 'application/pgp-signature',
		'sil'			=> 'audio/silk',
		'silo'			=> 'model/mesh',
		'sis'			=> 'application/vnd.symbian.install',
		'sisx'			=> 'application/vnd.symbian.install',
		'sit'			=> 'application/x-stuffit',
		'sitx'			=> 'application/x-stuffitx',
		'skd'			=> 'application/vnd.koan',
		'skm'			=> 'application/vnd.koan',
		'skp'			=> 'application/vnd.koan',
		'skt'			=> 'application/vnd.koan',
		'sldm'			=> 'application/vnd.ms-powerpoint.slide.macroenabled.12',
		'sldx'			=> 'application/vnd.openxmlformats-officedocument.presentationml.slide',
		'slt'			=> 'application/vnd.epson.salt',
		'sm'			=> 'application/vnd.stepmania.stepchart',
		'smf'			=> 'application/vnd.stardivision.math',
		'smi'			=> 'application/smil+xml',
		'smil'			=> 'application/smil+xml',
		'smv'			=> 'video/x-smv',
		'smzip'			=> 'application/vnd.stepmania.package',
		'snd'			=> 'audio/basic',
		'snf'			=> 'application/x-font-snf',
		'so'			=> 'application/octet-stream',
		'spc'			=> 'application/x-pkcs7-certificates',
		'spf'			=> 'application/vnd.yamaha.smaf-phrase',
		'spl'			=> 'application/x-futuresplash',
		'spot'			=> 'text/vnd.in3d.spot',
		'spp'			=> 'application/scvp-vp-response',
		'spq'			=> 'application/scvp-vp-request',
		'spx'			=> 'audio/ogg',
		'sql'			=> 'application/x-sql',
		'src'			=> 'application/x-wais-source',
		'srt'			=> 'application/x-subrip',
		'sru'			=> 'application/sru+xml',
		'srx'			=> 'application/sparql-results+xml',
		'ssdl'			=> 'application/ssdl+xml',
		'sse'			=> 'application/vnd.kodak-descriptor',
		'ssf'			=> 'application/vnd.epson.ssf',
		'ssml'			=> 'application/ssml+xml',
		'st'			=> 'application/vnd.sailingtracker.track',
		'stc'			=> 'application/vnd.sun.xml.calc.template',
		'std'			=> 'application/vnd.sun.xml.draw.template',
		'stf'			=> 'application/vnd.wt.stf',
		'sti'			=> 'application/vnd.sun.xml.impress.template',
		'stk'			=> 'application/hyperstudio',
		'stl'			=> 'application/vnd.ms-pki.stl',
		'str'			=> 'application/vnd.pg.format',
		'stw'			=> 'application/vnd.sun.xml.writer.template',
		'sub'			=> 'text/vnd.dvb.subtitle',
		'sus'			=> 'application/vnd.sus-calendar',
		'susp'			=> 'application/vnd.sus-calendar',
		'sv4cpio'		=> 'application/x-sv4cpio',
		'sv4crc'		=> 'application/x-sv4crc',
		'svc'			=> 'application/vnd.dvb.service',
		'svd'			=> 'application/vnd.svd',
		'svg'			=> 'image/svg+xml',
		'svgz'			=> 'image/svg+xml',
		'swa'			=> 'application/x-director',
		'swf'			=> 'application/x-shockwave-flash',
		'swi'			=> 'application/vnd.aristanetworks.swi',
		'sxc'			=> 'application/vnd.sun.xml.calc',
		'sxd'			=> 'application/vnd.sun.xml.draw',
		'sxg'			=> 'application/vnd.sun.xml.writer.global',
		'sxi'			=> 'application/vnd.sun.xml.impress',
		'sxm'			=> 'application/vnd.sun.xml.math',
		'sxw'			=> 'application/vnd.sun.xml.writer',
		't'				=> 'text/troff',
		't3'			=> 'application/x-t3vm-image',
		'taglet'		=> 'application/vnd.mynfc',
		'tao'			=> 'application/vnd.tao.intent-module-archive',
		'tar'			=> 'application/x-tar',
		'tcap'			=> 'application/vnd.3gpp2.tcap',
		'tcl'			=> 'application/x-tcl',
		'teacher'		=> 'application/vnd.smart.teacher',
		'tei'			=> 'application/tei+xml',
		'teicorpus'		=> 'application/tei+xml',
		'tex'			=> 'application/x-tex',
		'texi'			=> 'application/x-texinfo',
		'texinfo'		=> 'application/x-texinfo',
		'text'			=> 'text/plain',
		'tfi'			=> 'application/thraud+xml',
		'tfm'			=> 'application/x-tex-tfm',
		'tga'			=> 'image/x-tga',
		'thmx'			=> 'application/vnd.ms-officetheme',
		'tif'			=> 'image/tiff',
		'tiff'			=> 'image/tiff',
		'tmo'			=> 'application/vnd.tmobile-livetv',
		'torrent'		=> 'application/x-bittorrent',
		'tpl'			=> 'application/vnd.groove-tool-template',
		'tpt'			=> 'application/vnd.trid.tpt',
		'tr'			=> 'text/troff',
		'tra'			=> 'application/vnd.trueapp',
		'trm'			=> 'application/x-msterminal',
		'tsd'			=> 'application/timestamped-data',
		'tsv'			=> 'text/tab-separated-values',
		'ttc'			=> 'font/collection',
		'ttf'			=> 'font/ttf',
		'ttl'			=> 'text/turtle',
		'twd'			=> 'application/vnd.simtech-mindmapper',
		'twds'			=> 'application/vnd.simtech-mindmapper',
		'txd'			=> 'application/vnd.genomatix.tuxedo',
		'txf'			=> 'application/vnd.mobius.txf',
		'txt'			=> 'text/plain',
		'u32'			=> 'application/x-authorware-bin',
		'udeb'			=> 'application/x-debian-package',
		'ufd'			=> 'application/vnd.ufdl',
		'ufdl'			=> 'application/vnd.ufdl',
		'ulx'			=> 'application/x-glulx',
		'umj'			=> 'application/vnd.umajin',
		'unityweb'		=> 'application/vnd.unity',
		'uoml'			=> 'application/vnd.uoml+xml',
		'uri'			=> 'text/uri-list',
		'uris'			=> 'text/uri-list',
		'urls'			=> 'text/uri-list',
		'ustar'			=> 'application/x-ustar',
		'utz'			=> 'application/vnd.uiq.theme',
		'uu'			=> 'text/x-uuencode',
		'uva'			=> 'audio/vnd.dece.audio',
		'uvd'			=> 'application/vnd.dece.data',
		'uvf'			=> 'application/vnd.dece.data',
		'uvg'			=> 'image/vnd.dece.graphic',
		'uvh'			=> 'video/vnd.dece.hd',
		'uvi'			=> 'image/vnd.dece.graphic',
		'uvm'			=> 'video/vnd.dece.mobile',
		'uvp'			=> 'video/vnd.dece.pd',
		'uvs'			=> 'video/vnd.dece.sd',
		'uvt'			=> 'application/vnd.dece.ttml+xml',
		'uvu'			=> 'video/vnd.uvvu.mp4',
		'uvv'			=> 'video/vnd.dece.video',
		'uvva'			=> 'audio/vnd.dece.audio',
		'uvvd'			=> 'application/vnd.dece.data',
		'uvvf'			=> 'application/vnd.dece.data',
		'uvvg'			=> 'image/vnd.dece.graphic',
		'uvvh'			=> 'video/vnd.dece.hd',
		'uvvi'			=> 'image/vnd.dece.graphic',
		'uvvm'			=> 'video/vnd.dece.mobile',
		'uvvp'			=> 'video/vnd.dece.pd',
		'uvvs'			=> 'video/vnd.dece.sd',
		'uvvt'			=> 'application/vnd.dece.ttml+xml',
		'uvvu'			=> 'video/vnd.uvvu.mp4',
		'uvvv'			=> 'video/vnd.dece.video',
		'uvvx'			=> 'application/vnd.dece.unspecified',
		'uvvz'			=> 'application/vnd.dece.zip',
		'uvx'			=> 'application/vnd.dece.unspecified',
		'uvz'			=> 'application/vnd.dece.zip',
		'vcard'			=> 'text/vcard',
		'vcd'			=> 'application/x-cdlink',
		'vcf'			=> 'text/x-vcard',
		'vcg'			=> 'application/vnd.groove-vcard',
		'vcs'			=> 'text/x-vcalendar',
		'vcx'			=> 'application/vnd.vcx',
		'vis'			=> 'application/vnd.visionary',
		'viv'			=> 'video/vnd.vivo',
		'vob'			=> 'video/x-ms-vob',
		'vor'			=> 'application/vnd.stardivision.writer',
		'vox'			=> 'application/x-authorware-bin',
		'vrml'			=> 'model/vrml',
		'vsd'			=> 'application/vnd.visio',
		'vsf'			=> 'application/vnd.vsf',
		'vss'			=> 'application/vnd.visio',
		'vst'			=> 'application/vnd.visio',
		'vsw'			=> 'application/vnd.visio',
		'vtu'			=> 'model/vnd.vtu',
		'vxml'			=> 'application/voicexml+xml',
		'w3d'			=> 'application/x-director',
		'wad'			=> 'application/x-doom',
		'wav'			=> 'audio/x-wav',
		'wax'			=> 'audio/x-ms-wax',
		'wbmp'			=> 'image/vnd.wap.wbmp',
		'wbs'			=> 'application/vnd.criticaltools.wbs+xml',
		'wbxml'			=> 'application/vnd.wap.wbxml',
		'wcm'			=> 'application/vnd.ms-works',
		'wdb'			=> 'application/vnd.ms-works',
		'wdp'			=> 'image/vnd.ms-photo',
		'weba'			=> 'audio/webm',
		'webm'			=> 'video/webm',
		'webp'			=> 'image/webp',
		'wg'			=> 'application/vnd.pmi.widget',
		'wgt'			=> 'application/widget',
		'wks'			=> 'application/vnd.ms-works',
		'wm'			=> 'video/x-ms-wm',
		'wma'			=> 'audio/x-ms-wma',
		'wmd'			=> 'application/x-ms-wmd',
		'wmf'			=> 'application/x-msmetafile',
		'wml'			=> 'text/vnd.wap.wml',
		'wmlc'			=> 'application/vnd.wap.wmlc',
		'wmls'			=> 'text/vnd.wap.wmlscript',
		'wmlsc'			=> 'application/vnd.wap.wmlscriptc',
		'wmv'			=> 'video/x-ms-wmv',
		'wmx'			=> 'video/x-ms-wmx',
		'wmz'			=> 'application/x-msmetafile',
		'woff'			=> 'font/woff',
		'woff2'			=> 'font/woff2',
		'wpd'			=> 'application/vnd.wordperfect',
		'wpl'			=> 'application/vnd.ms-wpl',
		'wps'			=> 'application/vnd.ms-works',
		'wqd'			=> 'application/vnd.wqd',
		'wri'			=> 'application/x-mswrite',
		'wrl'			=> 'model/vrml',
		'wsdl'			=> 'application/wsdl+xml',
		'wspolicy'		=> 'application/wspolicy+xml',
		'wtb'			=> 'application/vnd.webturbo',
		'wvx'			=> 'video/x-ms-wvx',
		'x32'			=> 'application/x-authorware-bin',
		'x3d'			=> 'model/x3d+xml',
		'x3db'			=> 'model/x3d+binary',
		'x3dbz'			=> 'model/x3d+binary',
		'x3dv'			=> 'model/x3d+vrml',
		'x3dvz'			=> 'model/x3d+vrml',
		'x3dz'			=> 'model/x3d+xml',
		'xaml'			=> 'application/xaml+xml',
		'xap'			=> 'application/x-silverlight-app',
		'xar'			=> 'application/vnd.xara',
		'xbap'			=> 'application/x-ms-xbap',
		'xbd'			=> 'application/vnd.fujixerox.docuworks.binder',
		'xbm'			=> 'image/x-xbitmap',
		'xdf'			=> 'application/xcap-diff+xml',
		'xdm'			=> 'application/vnd.syncml.dm+xml',
		'xdp'			=> 'application/vnd.adobe.xdp+xml',
		'xdssc'			=> 'application/dssc+xml',
		'xdw'			=> 'application/vnd.fujixerox.docuworks',
		'xenc'			=> 'application/xenc+xml',
		'xer'			=> 'application/patch-ops-error+xml',
		'xfdf'			=> 'application/vnd.adobe.xfdf',
		'xfdl'			=> 'application/vnd.xfdl',
		'xht'			=> 'application/xhtml+xml',
		'xhtml'			=> 'application/xhtml+xml',
		'xhvml'			=> 'application/xv+xml',
		'xif'			=> 'image/vnd.xiff',
		'xla'			=> 'application/vnd.ms-excel',
		'xlam'			=> 'application/vnd.ms-excel.addin.macroenabled.12',
		'xlc'			=> 'application/vnd.ms-excel',
		'xlf'			=> 'application/x-xliff+xml',
		'xlm'			=> 'application/vnd.ms-excel',
		'xls'			=> 'application/vnd.ms-excel',
		'xlsb'			=> 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
		'xlsm'			=> 'application/vnd.ms-excel.sheet.macroenabled.12',
		'xlsx'			=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xlt'			=> 'application/vnd.ms-excel',
		'xltm'			=> 'application/vnd.ms-excel.template.macroenabled.12',
		'xltx'			=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'xlw'			=> 'application/vnd.ms-excel',
		'xm'			=> 'audio/xm',
		'xml'			=> 'application/xml',
		'xo'			=> 'application/vnd.olpc-sugar',
		'xop'			=> 'application/xop+xml',
		'xpi'			=> 'application/x-xpinstall',
		'xpl'			=> 'application/xproc+xml',
		'xpm'			=> 'image/x-xpixmap',
		'xpr'			=> 'application/vnd.is-xpr',
		'xps'			=> 'application/vnd.ms-xpsdocument',
		'xpw'			=> 'application/vnd.intercon.formnet',
		'xpx'			=> 'application/vnd.intercon.formnet',
		'xsl'			=> 'application/xml',
		'xslt'			=> 'application/xslt+xml',
		'xsm'			=> 'application/vnd.syncml+xml',
		'xspf'			=> 'application/xspf+xml',
		'xul'			=> 'application/vnd.mozilla.xul+xml',
		'xvm'			=> 'application/xv+xml',
		'xvml'			=> 'application/xv+xml',
		'xwd'			=> 'image/x-xwindowdump',
		'xyz'			=> 'chemical/x-xyz',
		'xz'			=> 'application/x-xz',
		'yang'			=> 'application/yang',
		'yin'			=> 'application/yin+xml',
		'z1'			=> 'application/x-zmachine',
		'z2'			=> 'application/x-zmachine',
		'z3'			=> 'application/x-zmachine',
		'z4'			=> 'application/x-zmachine',
		'z5'			=> 'application/x-zmachine',
		'z6'			=> 'application/x-zmachine',
		'z7'			=> 'application/x-zmachine',
		'z8'			=> 'application/x-zmachine',
		'zaz'			=> 'application/vnd.zzazz.deck+xml',
		'zip'			=> 'application/zip',
		'zir'			=> 'application/vnd.zul',
		'zirz'			=> 'application/vnd.zul',
		'zmm'			=> 'application/vnd.handheld-entertainment+xml'
	);

	/**
	 * Get the mime type based on file extension
	 * 
	 * @param   string   $file The file name or path
	 *
	 * @return  string the mime type on success
	 * 
	 */
	public static function mimeType($file)
	{
		/**
		 *                  **DISCLAIMER**
		 * This will just match the file extension to the following
		 * array. It does not guarantee that the file is TRULY that
		 * of the extension that this function returns.
		 * https://gist.github.com/Llewellynvdm/74be373357e131b8775a7582c3de508b
		 */		

		// get the extension form file
		$extension = \strtolower(\pathinfo($file, \PATHINFO_EXTENSION));
		// check if we have the extension listed
		if (isset(self::$fileExtensionToMimeType[$extension]))
		{
			return self::$fileExtensionToMimeType[$extension];
		}
		elseif (function_exists('mime_content_type'))
		{
			return mime_content_type($file);
		}
		elseif (function_exists('finfo_open'))
		{
			$finfo	= finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mimetype;
		}
		return 'application/octet-stream';
	}

	/**
	 * Get the file extensions
	 * 
	 * @param   string    $target   The targeted/filter option
	 * @param   boolean   $sorted   The multidimensional grouping sort (only if targeted filter is used)
	 *
	 * @return  array     All the extensions (targeted & sorted)
	 * 
	 */
	public static function getFileExtensions($target = null, $sorted = false)
	{
		// we have some in-house grouping/filters :)
		$filters = array(
			'image' => array('image', 'font', 'model'),
			'document' => array('application', 'text', 'chemical', 'message'),
			'media' => array('video', 'audio'),
			'file' => array('image', 'application', 'text', 'video', 'audio'),
			'all' => array('application', 'text', 'chemical', 'message', 'image', 'font', 'model', 'video', 'audio', 'x-conference')
		);
		// sould we filter
		if ($target)
		{
			// the bucket to get extensions
			$fileextensions = array();
			// check if filter exist (if not return empty array)
			if (isset($filters[$target]))
			{
				foreach (self::$fileExtensionToMimeType as $extension => $mimetype)
				{
					// get the key mime type
					$mimearr = explode("/", $mimetype, 2);
					// check if this file extension should be added
					if (in_array($mimearr[0], $filters[$target]))
					{
						if ($sorted)
						{
							if (!isset($fileextensions[$mimearr[0]]))
							{
								$fileextensions[$mimearr[0]] = array();
							}
							$fileextensions[$mimearr[0]][$extension] = $extension;
						}
						else
						{
							$fileextensions[$extension] = $extension;
						}
					}
				}
			}
			return $fileextensions;
		}
		// we just return all file extensions
		return array_keys(self::$fileExtensionToMimeType);
	}

	/**
	 * Load the Composer Vendors
	 */
	public static function composerAutoload($target)
	{
		// insure we load the composer vendor only once
		if (!isset(self::$composer[$target]))
		{
			// get the function name
			$functionName = self::safeString('compose' . $target);
			// check if method exist
			if (method_exists(__CLASS__, $functionName))
			{
				return self::{$functionName}();
			}
			return false;
		}
		return self::$composer[$target];
	}

	/**
	 * Convert it into a string
	 */
	public static function jsonToString($value, $sperator = ", ", $table = null, $id = 'id', $name = 'name')
	{
		// do some table foot work
		$external = false;
		if (strpos($table, '#__') !== false)
		{
			$external = true;
			$table = str_replace('#__', '', $table);
		}
		// check if string is JSON
		$result = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE)
		{
			// is JSON
			if (self::checkArray($result))
			{
				if (self::checkString($table))
				{
					$names = array();
					foreach ($result as $val)
					{
						if ($external)
						{
							if ($_name = self::getVar(null, $val, $id, $name, '=', $table))
							{
								$names[] = $_name;
							}
						}
						else
						{
							if ($_name = self::getVar($table, $val, $id, $name))
							{
								$names[] = $_name;
							}
						}
					}
					if (self::checkArray($names))
					{
						return (string) implode($sperator,$names);
					}	
				}
				return (string) implode($sperator,$result);
			}
			return (string) json_decode($value);
		}
		return $value;
	}

	/**
	 * Load the Component xml manifest.
	 */
	public static function manifest()
	{
		$manifestUrl = JPATH_ADMINISTRATOR."/components/com_questionsanswers/questionsanswers.xml";
		return simplexml_load_file($manifestUrl);
	}

	/**
	 * Joomla version object
	 */	
	protected static $JVersion;

	/**
	 * set/get Joomla version
	 */
	public static function jVersion()
	{
		// check if set
		if (!self::checkObject(self::$JVersion))
		{
			self::$JVersion = new JVersion();
		}
		return self::$JVersion;
	}

	/**
	 * Load the Contributors details.
	 */
	public static function getContributors()
	{
		// get params
		$params	= JComponentHelper::getParams('com_questionsanswers');
		// start contributors array
		$contributors = array();
		// get all Contributors (max 20)
		$searchArray = range('0','20');
		foreach($searchArray as $nr)
		{
			if ((NULL !== $params->get("showContributor".$nr)) && ($params->get("showContributor".$nr) == 2 || $params->get("showContributor".$nr) == 3))
			{
				// set link based of selected option
				if($params->get("useContributor".$nr) == 1)
                                {
					$link_front = '<a href="mailto:'.$params->get("emailContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
                                elseif($params->get("useContributor".$nr) == 2)
                                {
					$link_front = '<a href="'.$params->get("linkContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
                                else
                                {
					$link_front = '';
					$link_back = '';
				}
				$contributors[$nr]['title']	= self::htmlEscape($params->get("titleContributor".$nr));
				$contributors[$nr]['name']	= $link_front.self::htmlEscape($params->get("nameContributor".$nr)).$link_back;
			}
		}
		return $contributors;
	}

	/**
	 *	Load the Component Help URLs.
	 **/
	public static function getHelpUrl($view)
	{
		$user	= JFactory::getUser();
		$groups = $user->get('groups');
		$db	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select(array('a.id','a.groups','a.target','a.type','a.article','a.url'));
		$query->from('#__questionsanswers_help_document AS a');
		$query->where('a.site_view = '.$db->quote($view));
		$query->where('a.location = 2');
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows())
		{
			$helps = $db->loadObjectList();
			if (self::checkArray($helps))
			{
				foreach ($helps as $nr => $help)
				{
					if ($help->target == 1)
					{
						$targetgroups = json_decode($help->groups, true);
						if (!array_intersect($targetgroups, $groups))
						{
							// if user not in those target groups then remove the item
							unset($helps[$nr]);
							continue;
						}
					}
					// set the return type
					switch ($help->type)
					{
						// set joomla article
						case 1:
							return self::loadArticleLink($help->article);
							break;
						// set help text
						case 2:
							return self::loadHelpTextLink($help->id);
							break;
						// set Link
						case 3:
							return $help->url;
							break;
					}
				}
			}
		}
		return false;
	}

	/**
	 *	Get the Article Link.
	 **/
	protected static function loadArticleLink($id)
	{
		return JURI::root().'index.php?option=com_content&view=article&id='.$id.'&tmpl=component&layout=modal';
	}

	/**
	 *	Get the Help Text Link.
	 **/
	protected static function loadHelpTextLink($id)
	{
		$token = JSession::getFormToken();
		return 'index.php?option=com_questionsanswers&task=help.getText&id=' . (int) $id . '&token=' . $token;
	}

	/**
	 * Get any component's model
	 */
	public static function getModel($name, $path = JPATH_COMPONENT_SITE, $Component = 'Questionsanswers', $config = array())
	{
		// fix the name
		$name = self::safeString($name);
		// full path to models
		$fullPathModels = $path . '/models';
		// load the model file
		JModelLegacy::addIncludePath($fullPathModels, $Component . 'Model');
		// make sure the table path is loaded
		if (!isset($config['table_path']) || !self::checkString($config['table_path']))
		{
			// This is the JCB default path to tables in Joomla 3.x
			$config['table_path'] = JPATH_ADMINISTRATOR . '/components/com_' . strtolower($Component) . '/tables';
		}
		// get instance
		$model = JModelLegacy::getInstance($name, $Component . 'Model', $config);
		// if model not found (strange)
		if ($model == false)
		{
			jimport('joomla.filesystem.file');
			// get file path
			$filePath = $path . '/' . $name . '.php';
			$fullPathModel = $fullPathModels . '/' . $name . '.php';
			// check if it exists
			if (JFile::exists($filePath))
			{
				// get the file
				require_once $filePath;
			}
			elseif (JFile::exists($fullPathModel))
			{
				// get the file
				require_once $fullPathModel;
			}
			// build class names
			$modelClass = $Component . 'Model' . $name;
			if (class_exists($modelClass))
			{
				// initialize the model
				return new $modelClass($config);
			}
		}
		return $model;
	}

	/**
	 * Add to asset Table
	 */
	public static function setAsset($id, $table, $inherit = true)
	{
		$parent = JTable::getInstance('Asset');
		$parent->loadByName('com_questionsanswers');
		
		$parentId = $parent->id;
		$name     = 'com_questionsanswers.'.$table.'.'.$id;
		$title    = '';

		$asset = JTable::getInstance('Asset');
		$asset->loadByName($name);

		// Check for an error.
		$error = $asset->getError();

		if ($error)
		{
			return false;
		}
		else
		{
			// Specify how a new or moved node asset is inserted into the tree.
			if ($asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;
			// get the default asset rules
			$rules = self::getDefaultAssetRules('com_questionsanswers', $table, $inherit);
			if ($rules instanceof JAccessRules)
			{
				$asset->rules = (string) $rules;
			}

			if (!$asset->check() || !$asset->store())
			{
				JFactory::getApplication()->enqueueMessage($asset->getError(), 'warning');
				return false;
			}
			else
			{
				// Create an asset_id or heal one that is corrupted.
				$object = new stdClass();

				// Must be a valid primary key value.
				$object->id = $id;
				$object->asset_id = (int) $asset->id;

				// Update their asset_id to link to the asset table.
				return JFactory::getDbo()->updateObject('#__questionsanswers_'.$table, $object, 'id');
			}
		}
		return false;
	}

	/**
	 * Gets the default asset Rules for a component/view.
	 */
	protected static function getDefaultAssetRules($component, $view, $inherit = true)
	{
		// if new or inherited
		$assetId = 0;
		// Only get the actual item rules if not inheriting
		if (!$inherit)
		{
			// Need to find the asset id by the name of the component.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . ' = ' . $db->quote($component));
			$db->setQuery($query);
			$db->execute();
			// check that there is a value
			if ($db->getNumRows())
			{
				// asset already set so use saved rules
				$assetId = (int) $db->loadResult();
			}
		}
		// get asset rules
		$result =  JAccess::getAssetRules($assetId);
		if ($result instanceof JAccessRules)
		{
			$_result = (string) $result;
			$_result = json_decode($_result);
			foreach ($_result as $name => &$rule)
			{
				$v = explode('.', $name);
				if ($view !== $v[0])
				{
					// remove since it is not part of this view
					unset($_result->$name);
				}
				elseif ($inherit)
				{
					// clear the value since we inherit
					$rule = array();
				}
			}
			// check if there are any view values remaining
			if (count((array) $_result))
			{
				$_result = json_encode($_result);
				$_result = array($_result);
				// Instantiate and return the JAccessRules object for the asset rules.
				$rules = new JAccessRules($_result);
				// return filtered rules
				return $rules;
			}
		}
		return $result;
	}

	/**
	 * xmlAppend
	 *
	 * @param   SimpleXMLElement   $xml      The XML element reference in which to inject a comment
	 * @param   mixed              $node     A SimpleXMLElement node to append to the XML element reference, or a stdClass object containing a comment attribute to be injected before the XML node and a fieldXML attribute containing a SimpleXMLElement
	 *
	 * @return  null
	 *
	 */
	public static function xmlAppend(&$xml, $node)
	{
		if (!$node)
		{
			// element was not returned
			return;
		}
		switch (get_class($node))
		{
			case 'stdClass':
				if (property_exists($node, 'comment'))
				{
					self::xmlComment($xml, $node->comment);
				}
				if (property_exists($node, 'fieldXML'))
				{
					self::xmlAppend($xml, $node->fieldXML);
				}
				break;
			case 'SimpleXMLElement':
				$domXML = dom_import_simplexml($xml);
				$domNode = dom_import_simplexml($node);
				$domXML->appendChild($domXML->ownerDocument->importNode($domNode, true));
				$xml = simplexml_import_dom($domXML);
				break;
		}
	}

	/**
	 * xmlComment
	 *
	 * @param   SimpleXMLElement   $xml        The XML element reference in which to inject a comment
	 * @param   string             $comment    The comment to inject
	 *
	 * @return  null
	 *
	 */
	public static function xmlComment(&$xml, $comment)
	{
		$domXML = dom_import_simplexml($xml);
		$domComment = new DOMComment($comment);
		$nodeTarget = $domXML->ownerDocument->importNode($domComment, true);
		$domXML->appendChild($nodeTarget);
		$xml = simplexml_import_dom($domXML);
	}

	/**
	 * xmlAddAttributes
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $attributes   The attributes to apply to the XML element
	 *
	 * @return  null
	 *
	 */
	public static function xmlAddAttributes(&$xml, $attributes = array())
	{
		foreach ($attributes as $key => $value)
		{
			$xml->addAttribute($key, $value);
		}
	}

	/**
	 * xmlAddOptions
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $options      The options to apply to the XML element
	 *
	 * @return  void
	 *
	 */
	public static function xmlAddOptions(&$xml, $options = array())
	{
		foreach ($options as $key => $value)
		{
			$addOption = $xml->addChild('option');
			$addOption->addAttribute('value', $key);
			$addOption[] = $value;
		}
	}

	/**
	 * get the field object
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   string     $default      The default of the field
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 *
	 */
	public static function getFieldObject(&$attributes, $default = '', $options = null)
	{
		// make sure we have attributes and a type value
		if (self::checkArray($attributes) && isset($attributes['type']))
		{
			// make sure the form helper class is loaded
			if (!method_exists('JFormHelper', 'loadFieldType'))
			{
				jimport('joomla.form.form');
			}
			// get field type
			$field = JFormHelper::loadFieldType($attributes['type'], true);
			// get field xml
			$XML = self::getFieldXML($attributes, $options);
			// setup the field
			$field->setup($XML, $default);
			// return the field object
			return $field;
		}
		return false;
	}

	/**
	 * get the field xml
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 *
	 */
	public static function getFieldXML(&$attributes, $options = null)
	{
		// make sure we have attributes and a type value
		if (self::checkArray($attributes))
		{
			// start field xml
			$XML = new SimpleXMLElement('<field/>');
			// load the attributes
			self::xmlAddAttributes($XML, $attributes);
			// check if we have options
			if (self::checkArray($options))
			{
				// load the options
				self::xmlAddOptions($XML, $options);
			}
			// return the field xml
			return $XML;
		}
		return false;
	}

	/**
	 * Render Bool Button
	 *
	 * @param   array   $args   All the args for the button
	 *                             0) name
	 *                             1) additional (options class) // not used at this time
	 *                             2) default
	 *                             3) yes (name)
	 *                             4) no (name)
	 *
	 * @return  string    The input html of the button
	 *
	 */
	public static function renderBoolButton()
	{
		$args = func_get_args();
		// check if there is additional button class
		$additional = isset($args[1]) ? (string) $args[1] : ''; // not used at this time
		// button attributes
		$buttonAttributes = array(
			'type' => 'radio',
			'name' => isset($args[0]) ? self::htmlEscape($args[0]) : 'bool_button',
			'label' => isset($args[0]) ? self::safeString(self::htmlEscape($args[0]), 'Ww') : 'Bool Button', // not seen anyway
			'class' => 'btn-group',
			'filter' => 'INT',
			'default' => isset($args[2]) ? (int) $args[2] : 0);
		// set the button options
		$buttonOptions = array(
			'1' => isset($args[3]) ? self::htmlEscape($args[3]) : 'JYES',
			'0' => isset($args[4]) ? self::htmlEscape($args[4]) : 'JNO');
		// return the input
		return self::getFieldObject($buttonAttributes, $buttonAttributes['default'], $buttonOptions)->input;
	}

	/**
	 *  UIKIT Component Classes
	 **/
	public static $uk_components = array(
			'data-uk-grid' => array(
				'grid' ),
			'uk-accordion' => array(
				'accordion' ),
			'uk-autocomplete' => array(
				'autocomplete' ),
			'data-uk-datepicker' => array(
				'datepicker' ),
			'uk-form-password' => array(
				'form-password' ),
			'uk-form-select' => array(
				'form-select' ),
			'data-uk-htmleditor' => array(
				'htmleditor' ),
			'data-uk-lightbox' => array(
				'lightbox' ),
			'uk-nestable' => array(
				'nestable' ),
			'UIkit.notify' => array(
				'notify' ),
			'data-uk-parallax' => array(
				'parallax' ),
			'uk-search' => array(
				'search' ),
			'uk-slider' => array(
				'slider' ),
			'uk-slideset' => array(
				'slideset' ),
			'uk-slideshow' => array(
				'slideshow',
				'slideshow-fx' ),
			'uk-sortable' => array(
				'sortable' ),
			'data-uk-sticky' => array(
				'sticky' ),
			'data-uk-timepicker' => array(
				'timepicker' ),
			'data-uk-tooltip' => array(
				'tooltip' ),
			'uk-placeholder' => array(
				'placeholder' ),
			'uk-dotnav' => array(
				'dotnav' ),
			'uk-slidenav' => array(
				'slidenav' ),
			'uk-form' => array(
				'form-advanced' ),
			'uk-progress' => array(
				'progress' ),
			'upload-drop' => array(
				'upload', 'form-file' )
			);

	/**
	 *  Add UIKIT Components
	 **/
	public static $uikit = false;

	/**
	 *  Get UIKIT Components
	 **/
	public static function getUikitComp($content,$classes = array())
	{
		if (strpos($content,'class="uk-') !== false)
		{
			// reset
			$temp = array();
			foreach (self::$uk_components as $looking => $add)
			{
				if (strpos($content,$looking) !== false)
				{
					$temp[] = $looking;
				}
			}
			// make sure uikit is loaded to config
			if (strpos($content,'class="uk-') !== false)
			{
				self::$uikit = true;
			}
			// sorter
			if (self::checkArray($temp))
			{
				// merger
				if (self::checkArray($classes))
				{
					$newTemp = array_merge($temp,$classes);
					$temp = array_unique($newTemp);
				}
				return $temp;
			}
		}
		if (self::checkArray($classes))
		{
			return $classes;
		}
		return false;
	}

	/**
	 * Get a variable 
	 *
	 * @param   string   $table        The table from which to get the variable
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 *
	 * @return  mix string/int/float
	 *
	 */
	public static function getVar($table, $where = null, $whereString = 'user', $what = 'id', $operator = '=', $main = 'questionsanswers')
	{
		if(!$where)
		{
			$where = JFactory::getUser()->id;
		}
		// Get a db connection.
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array($what)));		
		if (empty($table))
		{
			$query->from($db->quoteName('#__'.$main));
		}
		else
		{
			$query->from($db->quoteName('#__'.$main.'_'.$table));
		}
		if (is_numeric($where))
		{
			$query->where($db->quoteName($whereString) . ' '.$operator.' '.(int) $where);
		}
		elseif (is_string($where))
		{
			$query->where($db->quoteName($whereString) . ' '.$operator.' '. $db->quote((string)$where));
		}
		else
		{
			return false;
		}
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadResult();
		}
		return false;
	}

	/**
	 * Get array of variables
	 *
	 * @param   string   $table        The table from which to get the variables
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 * @param   bool     $unique       The switch to return a unique array
	 *
	 * @return  array
	 *
	 */
	public static function getVars($table, $where = null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = 'questionsanswers', $unique = true)
	{
		if(!$where)
		{
			$where = JFactory::getUser()->id;
		}

		if (!self::checkArray($where) && $where > 0)
		{
			$where = array($where);
		}

		if (self::checkArray($where))
		{
			// prep main <-- why? well if $main='' is empty then $table can be categories or users
			if (self::checkString($main))
			{
				$main = '_'.ltrim($main, '_');
			}
			// Get a db connection.
			$db = JFactory::getDbo();
			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array($what)));
			if (empty($table))
			{
				$query->from($db->quoteName('#__'.$main));
			}
			else
			{
				$query->from($db->quoteName('#_'.$main.'_'.$table));
			}
			// add strings to array search
			if ('IN_STRINGS' === $operator || 'NOT IN_STRINGS' === $operator)
			{
				$query->where($db->quoteName($whereString) . ' ' . str_replace('_STRINGS', '', $operator) . ' ("' . implode('","',$where) . '")');
			}
			else
			{
				$query->where($db->quoteName($whereString) . ' ' . $operator . ' (' . implode(',',$where) . ')');
			}
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				if ($unique)
				{
					return array_unique($db->loadColumn());
				}
				return $db->loadColumn();
			}
		}
		return false;
	} 

	public static function isPublished($id,$type)
	{
		if ($type == 'raw')
		{
			$type = 'item';
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.published'));
		$query->from('#__questionsanswers_'.$type.' AS a');
		$query->where('a.id = '. (int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return true;
		}
		return false;
	}

	public static function getGroupName($id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(array('a.title'));
		$query->from('#__usergroups AS a');
		$query->where('a.id = '. (int) $id);
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return $db->loadResult();
		}
		return $id;
	}

	/**
	 * Get the action permissions
	 *
	 * @param  string   $view        The related view name
	 * @param  int      $record      The item to act upon
	 * @param  string   $views       The related list view name
	 * @param  mixed    $target      Only get this permission (like edit, create, delete)
	 * @param  string   $component   The target component
	 * @param  object   $user        The user whose permissions we are loading
	 *
	 * @return  object   The JObject of permission/authorised actions
	 * 
	 */
	public static function getActions($view, &$record = null, $views = null, $target = null, $component = 'questionsanswers', $user = 'null')
	{
		// load the user if not given
		if (!self::checkObject($user))
		{
			// get the user object
			$user = JFactory::getUser();
		}
		// load the JObject
		$result = new JObject;
		// make view name safe (just incase)
		$view = self::safeString($view);
		if (self::checkString($views))
		{
			$views = self::safeString($views);
 		}
		// get all actions from component
		$actions = JAccess::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/com_' . $component . '/access.xml',
			"/access/section[@name='component']/"
		);
		// if non found then return empty JObject
		if (empty($actions))
		{
			return $result;
		}
		// get created by if not found
		if (self::checkObject($record) && !isset($record->created_by) && isset($record->id))
		{
			$record->created_by = self::getVar($view, $record->id, 'id', 'created_by', '=', $component);
		}
		// set actions only set in component settings
		$componentActions = array('core.admin', 'core.manage', 'core.options', 'core.export');
		// check if we have a target
		$checkTarget = false;
		if ($target)
		{
			// convert to an array
			if (self::checkString($target))
			{
				$target = array($target);
			}
			// check if we are good to go
			if (self::checkArray($target))
			{
				$checkTarget = true;
			}
		}
		// loop the actions and set the permissions
		foreach ($actions as $action)
		{
			// check target action filter
			if ($checkTarget && self::filterActions($view, $action->name, $target))
			{
				continue;
			}
			// set to use component default
			$fallback = true;
			// reset permission per/action
			$permission = false;
			$catpermission = false;
			// set area
			$area = 'comp';
			// check if the record has an ID and the action is item related (not a component action)
			if (self::checkObject($record) && isset($record->id) && $record->id > 0 && !in_array($action->name, $componentActions) &&
				(strpos($action->name, 'core.') !== false || strpos($action->name, $view . '.') !== false))
			{
				// we are in item
				$area = 'item';
				// The record has been set. Check the record permissions.
				$permission = $user->authorise($action->name, 'com_' . $component . '.' . $view . '.' . (int) $record->id);
				// if no permission found, check edit own
				if (!$permission)
				{
					// With edit, if the created_by matches current user then dig deeper.
					if (($action->name === 'core.edit' || $action->name === $view . '.edit') && $record->created_by > 0 && ($record->created_by == $user->id))
					{
						// the correct target
						$coreCheck = (array) explode('.', $action->name);
						// check that we have both local and global access
						if ($user->authorise($coreCheck[0] . '.edit.own', 'com_' . $component . '.' . $view . '.' . (int) $record->id) &&
							$user->authorise($coreCheck[0]  . '.edit.own', 'com_' . $component))
						{
							// allow edit
							$result->set($action->name, true);
							// set not to use global default
							// because we already validated it
							$fallback = false;
						}
						else
						{
							// do not allow edit
							$result->set($action->name, false);
							$fallback = false;
						}
					}
				}
				elseif (self::checkString($views) && isset($record->catid) && $record->catid > 0)
				{
					// we are in item
					$area = 'category';
					// set the core check
					$coreCheck = explode('.', $action->name);
					$core = $coreCheck[0];
					// make sure we use the core. action check for the categories
					if (strpos($action->name, $view) !== false && strpos($action->name, 'core.') === false )
					{
						$coreCheck[0] = 'core';
						$categoryCheck = implode('.', $coreCheck);
					}
					else
					{
						$categoryCheck = $action->name;
					}
					// The record has a category. Check the category permissions.
					$catpermission = $user->authorise($categoryCheck, 'com_' . $component . '.' . $views . '.category.' . (int) $record->catid);
					if (!$catpermission && !is_null($catpermission))
					{
						// With edit, if the created_by matches current user then dig deeper.
						if (($action->name === 'core.edit' || $action->name === $view . '.edit') && $record->created_by > 0 && ($record->created_by == $user->id))
						{
							// check that we have both local and global access
							if ($user->authorise('core.edit.own', 'com_' . $component . '.' . $views . '.category.' . (int) $record->catid) &&
								$user->authorise($core . '.edit.own', 'com_' . $component))
							{
								// allow edit
								$result->set($action->name, true);
								// set not to use global default
								// because we already validated it
								$fallback = false;
							}
							else
							{
								// do not allow edit
								$result->set($action->name, false);
								$fallback = false;
							}
						}
					}
				}
			}
			// if allowed then fallback on component global settings
			if ($fallback)
			{
				// if item/category blocks access then don't fall back on global
				if ((($area === 'item') && !$permission) || (($area === 'category') && !$catpermission))
				{
					// do not allow
					$result->set($action->name, false);
				}
				// Finally remember the global settings have the final say. (even if item allow)
				// The local item permissions can block, but it can't open and override of global permissions.
				// Since items are created by users and global permissions is set by system admin.
				else
				{
					$result->set($action->name, $user->authorise($action->name, 'com_' . $component));
				}
			}
		}
		return $result;
	}

	/**
	 * Filter the action permissions
	 *
	 * @param  string   $action   The action to check
	 * @param  array    $targets  The array of target actions
	 *
	 * @return  boolean   true if action should be filtered out
	 * 
	 */
	protected static function filterActions(&$view, &$action, &$targets)
	{
		foreach ($targets as $target)
		{
			if (strpos($action, $view . '.' . $target) !== false ||
				strpos($action, 'core.' . $target) !== false)
			{
				return false;
				break;
			}
		}
		return true;
	}

	/**
	 * Check if have an json string
	 *
	 * @input	string   The json string to check
	 *
	 * @returns bool true on success
	 */
	public static function checkJson($string)
	{
		if (self::checkString($string))
		{
			json_decode($string);
			return (json_last_error() === JSON_ERROR_NONE);
		}
		return false;
	}

	/**
	 * Check if have an object with a length
	 *
	 * @input	object   The object to check
	 *
	 * @returns bool true on success
	 */
	public static function checkObject($object)
	{
		if (isset($object) && is_object($object))
		{
			return count((array)$object) > 0;
		}
		return false;
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input	array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 */
	public static function checkArray($array, $removeEmptyString = false)
	{
		if (isset($array) && is_array($array) && ($nr = count((array)$array)) > 0)
		{
			// also make sure the empty strings are removed
			if ($removeEmptyString)
			{
				foreach ($array as $key => $string)
				{
					if (empty($string))
					{
						unset($array[$key]);
					}
				}
				return self::checkArray($array, false);
			}
			return $nr;
		}
		return false;
	}

	/**
	 * Check if have a string with a length
	 *
	 * @input	string   The string to check
	 *
	 * @returns bool true on success
	 */
	public static function checkString($string)
	{
		if (isset($string) && is_string($string) && strlen($string) > 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * Check if we are connected
	 * Thanks https://stackoverflow.com/a/4860432/1429677
	 *
	 * @returns bool true on success
	 */
	public static function isConnected()
	{
		// If example.com is down, then probably the whole internet is down, since IANA maintains the domain. Right?
		$connected = @fsockopen("www.example.com", 80); 
			// website, port  (try 80 or 443)
		if ($connected)
		{
			//action when connected
			$is_conn = true;
			fclose($connected);
		}
		else
		{
			//action in connection failure
			$is_conn = false;
		}
		return $is_conn;
	}

	/**
	 * Merge an array of array's
	 *
	 * @input	array   The arrays you would like to merge
	 *
	 * @returns array on success
	 */
	public static function mergeArrays($arrays)
	{
		if(self::checkArray($arrays))
		{
			$arrayBuket = array();
			foreach ($arrays as $array)
			{
				if (self::checkArray($array))
				{
					$arrayBuket = array_merge($arrayBuket, $array);
				}
			}
			return $arrayBuket;
		}
		return false;
	}

	// typo sorry!
	public static function sorten($string, $length = 40, $addTip = true)
	{
		return self::shorten($string, $length, $addTip);
	}

	/**
	 * Shorten a string
	 *
	 * @input	string   The you would like to shorten
	 *
	 * @returns string on success
	 */
	public static function shorten($string, $length = 40, $addTip = true)
	{
		if (self::checkString($string))
		{
			$initial = strlen($string);
			$words = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
			$words_count = count((array)$words);

			$word_length = 0;
			$last_word = 0;
			for (; $last_word < $words_count; ++$last_word)
			{
				$word_length += strlen($words[$last_word]);
				if ($word_length > $length)
				{
					break;
				}
			}

			$newString	= implode(array_slice($words, 0, $last_word));
			$final	= strlen($newString);
			if ($initial != $final && $addTip)
			{
				$title = self::shorten($string, 400 , false);
				return '<span class="hasTip" title="'.$title.'" style="cursor:help">'.trim($newString).'...</span>';
			}
			elseif ($initial != $final && !$addTip)
			{
				return trim($newString).'...';
			}
		}
		return $string;
	}

	/**
	 * Making strings safe (various ways)
	 *
	 * @input	string   The you would like to make safe
	 *
	 * @returns string on success
	 */
	public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = true, $keepOnlyCharacters = true)
	{
		if ($replaceNumbers === true)
		{
			// remove all numbers and replace with english text version (works well only up to millions)
			$string = self::replaceNumbers($string);
		}
		// 0nly continue if we have a string
		if (self::checkString($string))
		{
			// create file name without the extention that is safe
			if ($type === 'filename')
			{
				// make sure VDM is not in the string
				$string = str_replace('VDM', 'vDm', $string);
				// Remove anything which isn't a word, whitespace, number
				// or any of the following caracters -_()
				// If you don't need to handle multi-byte characters
				// you can use preg_replace rather than mb_ereg_replace
				// Thanks @Łukasz Rysiak!
				// $string = mb_ereg_replace("([^\w\s\d\-_\(\)])", '', $string);
				$string = preg_replace("([^\w\s\d\-_\(\)])", '', $string);
				// http://stackoverflow.com/a/2021729/1429677
				return preg_replace('/\s+/', ' ', $string);
			}
			// remove all other characters
			$string = trim($string);
			$string = preg_replace('/'.$spacer.'+/', ' ', $string);
			$string = preg_replace('/\s+/', ' ', $string);
			// Transliterate string
			$string = self::transliterate($string);
			// remove all and keep only characters
			if ($keepOnlyCharacters)
			{
				$string = preg_replace("/[^A-Za-z ]/", '', $string);
			}
			// keep both numbers and characters
			else
			{
				$string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);
			}
			// select final adaptations
			if ($type === 'L' || $type === 'strtolower')
			{
				// replace white space with underscore
				$string = preg_replace('/\s+/', $spacer, $string);
				// default is to return lower
				return strtolower($string);
			}
			elseif ($type === 'W')
			{
				// return a string with all first letter of each word uppercase(no undersocre)
				return ucwords(strtolower($string));
			}
			elseif ($type === 'w' || $type === 'word')
			{
				// return a string with all lowercase(no undersocre)
				return strtolower($string);
			}
			elseif ($type === 'Ww' || $type === 'Word')
			{
				// return a string with first letter of the first word uppercase and all the rest lowercase(no undersocre)
				return ucfirst(strtolower($string));
			}
			elseif ($type === 'WW' || $type === 'WORD')
			{
				// return a string with all the uppercase(no undersocre)
				return strtoupper($string);
			}
			elseif ($type === 'U' || $type === 'strtoupper')
			{
				// replace white space with underscore
				$string = preg_replace('/\s+/', $spacer, $string);
				// return all upper
				return strtoupper($string);
			}
			elseif ($type === 'F' || $type === 'ucfirst')
			{
				// replace white space with underscore
				$string = preg_replace('/\s+/', $spacer, $string);
				// return with first caracter to upper
				return ucfirst(strtolower($string));
			}
			elseif ($type === 'cA' || $type === 'cAmel' || $type === 'camelcase')
			{
				// convert all words to first letter uppercase
				$string = ucwords(strtolower($string));
				// remove white space
				$string = preg_replace('/\s+/', '', $string);
				// now return first letter lowercase
				return lcfirst($string);
			}
			// return string
			return $string;
		}
		// not a string
		return '';
	}

	public static function transliterate($string)
	{
		// set tag only once
		if (!self::checkString(self::$langTag))
		{
			// get global value
			self::$langTag = JComponentHelper::getParams('com_questionsanswers')->get('language', 'en-GB');
		}
		// Transliterate on the language requested
		$lang = Language::getInstance(self::$langTag);
		return $lang->transliterate($string);
	}

	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		if (self::checkString($var))
		{
			$filter = new JFilterInput();
			$string = $filter->clean(html_entity_decode(htmlentities($var, ENT_COMPAT, $charset)), 'HTML');
			if ($shorten)
			{
           		return self::shorten($string,$length);
			}
			return $string;
		}
		else
		{
			return '';
		}
	}

	public static function replaceNumbers($string)
	{
		// set numbers array
		$numbers = array();
		// first get all numbers
		preg_match_all('!\d+!', $string, $numbers);
		// check if we have any numbers
		if (isset($numbers[0]) && self::checkArray($numbers[0]))
		{
			foreach ($numbers[0] as $number)
			{
				$searchReplace[$number] = self::numberToString((int)$number);
			}
			// now replace numbers in string
			$string = str_replace(array_keys($searchReplace), array_values($searchReplace),$string);
			// check if we missed any, strange if we did.
			return self::replaceNumbers($string);
		}
		// return the string with no numbers remaining.
		return $string;
	}

	/**
	 * Convert an integer into an English word string
	 * Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
	 *
	 * @input	an int
	 * @returns a string
	 */
	public static function numberToString($x)
	{
		$nwords = array( "zero", "one", "two", "three", "four", "five", "six", "seven",
			"eight", "nine", "ten", "eleven", "twelve", "thirteen",
			"fourteen", "fifteen", "sixteen", "seventeen", "eighteen",
			"nineteen", "twenty", 30 => "thirty", 40 => "forty",
			50 => "fifty", 60 => "sixty", 70 => "seventy", 80 => "eighty",
			90 => "ninety" );

		if(!is_numeric($x))
		{
			$w = $x;
		}
		elseif(fmod($x, 1) != 0)
		{
			$w = $x;
		}
		else
		{
			if($x < 0)
			{
				$w = 'minus ';
				$x = -$x;
			}
			else
			{
				$w = '';
				// ... now $x is a non-negative integer.
			}

			if($x < 21)   // 0 to 20
			{
				$w .= $nwords[$x];
			}
			elseif($x < 100)  // 21 to 99
			{ 
				$w .= $nwords[10 * floor($x/10)];
				$r = fmod($x, 10);
				if($r > 0)
				{
					$w .= ' '. $nwords[$r];
				}
			}
			elseif($x < 1000)  // 100 to 999
			{
				$w .= $nwords[floor($x/100)] .' hundred';
				$r = fmod($x, 100);
				if($r > 0)
				{
					$w .= ' and '. self::numberToString($r);
				}
			}
			elseif($x < 1000000)  // 1000 to 999999
			{
				$w .= self::numberToString(floor($x/1000)) .' thousand';
				$r = fmod($x, 1000);
				if($r > 0)
				{
					$w .= ' ';
					if($r < 100)
					{
						$w .= 'and ';
					}
					$w .= self::numberToString($r);
				}
			} 
			else //  millions
			{    
				$w .= self::numberToString(floor($x/1000000)) .' million';
				$r = fmod($x, 1000000);
				if($r > 0)
				{
					$w .= ' ';
					if($r < 100)
					{
						$w .= 'and ';
					}
					$w .= self::numberToString($r);
				}
			}
		}
		return $w;
	}

	/**
	 * Random Key
	 *
	 * @returns a string
	 */
	public static function randomkey($size)
	{
		$bag = "abcefghijknopqrstuwxyzABCDDEFGHIJKLLMMNOPQRSTUVVWXYZabcddefghijkllmmnopqrstuvvwxyzABCEFGHIJKNOPQRSTUWXYZ";
		$key = array();
		$bagsize = strlen($bag) - 1;
		for ($i = 0; $i < $size; $i++)
		{
			$get = rand(0, $bagsize);
			$key[] = $bag[$get];
		}
		return implode($key);
	}

	/**
	 *	Get The Encryption Keys
	 *
	 *	@param  string        $type     The type of key
	 *	@param  string/bool   $default  The return value if no key was found
	 *
	 *	@return  string   On success
	 *
	 **/
	public static function getCryptKey($type, $default = false)
	{
		// Get the global params
		$params = JComponentHelper::getParams('com_questionsanswers', true);
		// Basic Encryption Type
		if ('basic' === $type)
		{
			$basic_key = $params->get('basic_key', $default);
			if (self::checkString($basic_key))
			{
				return $basic_key;
			}
		}

		return $default;
	}
}
