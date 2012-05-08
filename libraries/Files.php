<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Files
 * 
 * This class extends the file helper to make it easier to create files without having to worry
 * about whether directories exist.
 * 
 * It also makes it easy to save multiple variable types including strings, arrays, integers, and objects.
 * 
 *
 * @author      Eclarian Dev Team
 * @copyright   Eclarian LLC
 * @license		MIT
 * @version     1.0.2
 */
class Files {
	
	/**
	 * Loaded Cache
	 * 
	 * Stores the data from the requested filename and returns cache on subsequent calls
	 * 
	 * @since	1.0.0
	 * @var		array
	 * @access	protected
	 */
	protected $loaded_cache = array();
	
	/**
	 * Default Path (CONFIGURABLE)
	 * 
	 * @since	1.0.0
	 * @var		string
	 * @access	protected
	 */
	protected $files_path = '';
	
	/**
	 * Save as Config (CONFIGURABLE)
	 * 
	 * @since	1.0.0
	 * @var		boolean
	 * @access	protected
	 */
	protected $files_save_like = 'config';	
	
	/**
	 * Files Extension
	 * 
	 * @since	1.0.0
	 * @var		string
	 * @access	protected
	 */
	protected $files_ext = '.php';
	
	/**
	 * Full Configuration
	 * 
	 * @since	1.0.0
	 * @var		array
	 * @access	protected	
	 */
	protected $full_config;
	
	/**
	 * Files to Include
	 * 
	 * @since	1.0.0
	 * @var		array
	 * @access	protected
	 */
	protected $files_to_include = array('.php');
	
	/**
	 * File Info Values
	 * 
	 * @var		array
	 * @access	protected
	 */
	protected $files_info_values = array('name', 'server_path', 'size', 'date');
	
	/**
	 * Data to be Saved - Reset after every save
	 * 
	 * @var		mixed
	 * @access	private
	 */
	private $data = NULL;
	
	// -------------------------------------------------------------------------
	
	/**
	 * Setup Configuration
	 */
	public function __construct()
	{
		$CI =& get_instance();
		$CI->files_path = APPPATH . 'cache' . DIRECTORY_SEPARATOR; // Default location
		$CI->load->helper('file');
		$CI->load->config('files', TRUE);
		$this->full_config = $CI->config->item('files');
		$this->setup($this->full_config);
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Setup
	 * 
	 * Allows you to override the configuration. 
	 * The helper methods ext() and save_like() make it easy, so use those instead
	 * 
	 * @since	1.0.0
	 * @param	array
	 * @return	object
	 */
	public function setup($config)
	{
		foreach ($config as $var_name => $val)
		{
			if (property_exists($this, $var_name))
			{
				$this->$var_name = $val;
			}
		}
		
		return $this;
	}
	
	// -------------------------------------------------------------------------
	
	/** 
	 * Extension
	 * 
	 * @since	1.0.0
	 * @param	string	Must contain period (".php")
	 * @return	object
	 */
	public function ext($e = '.php')
	{
		$this->files_ext = $e;
		return $this;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Like
	 * 
	 * Allows you to specify how you want the PHP file to save
	 * 
	 * @param	string	"config", "lang" or ""
	 * @return	object
	 */
	public function like($type = 'config')
	{
		$this->files_save_like = $type;
		return $this;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Data
	 * 
	 * Supports everything supported by var_export()
	 * 
	 * @param	mixed
	 * @return	object
	 */
	public function data($data)
	{
		$this->data = $data;
		return $this;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Read
	 * 
	 * By default, this function will include PHP files to execute as opposed to
	 * just reading their contents. All other files it will just read the contents
	 * of the file.
	 * 
	 * @since	1.0.0
	 * @param	string	Filename
	 * @param	string	Variable Name or Key Name
	 * @param	boolean	Whether the directory provided is the FULL directory
	 * @return	array	FALSE on failure
	 */
	public function read($filename, $var_name = 'files_data', $is_full_path = FALSE)
	{
		$this->data = NULL;
		$filename = $this->parse_filename($filename, $is_full_path);
		if ( ! file_exists($filename))
		{
			log_message('error', "Files::read() - File does not exist - $filename ");
			return FALSE;
		}
		
		// Return the values from cache if they exist
		if (isset($this->loaded_cache[$filename]))
		{
			// @since 1.0.2 - Fixes the issue of PHP returning true from an isset check like the following:
			// $stupid = "this is an example string";
			// var_dump(isset($stupid['abc'])); -> THIS IS TRUE!
			return ( ! empty($var_name) && ! is_string($this->loaded_cache[$filename]) && isset($this->loaded_cache[$filename][$var_name])) ? 
				$this->loaded_cache[$filename][$var_name] :
				$this->loaded_cache[$filename];
		}		
		
		// Include and Evaluate defined files
		if (in_array($this->files_ext, $this->files_to_include))
		{
			// Assumes that $var_name is an array key if saving like config or lang
			$var = $var_name;
			if ( ! empty($this->files_save_like))
			{
				$var = $this->files_save_like;
			}
			
			$outcome = include $filename; // $outcome will be int(1) if successful
			
			// Check if variable is found from file we just included
			if (isset($$var))
			{
				$this->loaded_cache[$filename] = $$var;
			}
			else
			{
				$msg = ($outcome !== 1) ? 
					"Files::read() - Failed to included file and read contents. $$var - $filename ":
					"Files::read() - Successfully included file but failed to read contents. $$var - $filename ";
				log_message('error', $msg);
				return FALSE;
			}
			
			// This will shove config and lang arrays onto their object properties
			$this->parse_codeigniter_data($this->loaded_cache[$filename]);
		}
		else
		{
			// This will fetch the contents of the file as a string and return the string.
			$this->loaded_cache[$filename] = read_file($filename);
		}
		
		// Return entire data set or by $var_name if isset
		// Updated @version 1.0.1 to fix error where it would return partial strings of data
		return ( ! empty($var_name) && ! is_string($this->loaded_cache[$filename]) && isset($this->loaded_cache[$filename][$var_name])) ? 
			$this->loaded_cache[$filename][$var_name] :
			$this->loaded_cache[$filename];		
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Save
	 * 
	 * @since	1.0.0
	 * @param	string	Filename - Include path if it is to be saved in subfolder
	 * @param	string	Variable Name or Key Name
	 * @param	boolean	Whether the directory provided is the FULL directory
	 * @return	boolean
	 */
	public function save($filename, $var_name = 'files_data', $is_full_path = FALSE)
	{
		$this->create_directory(explode(DIRECTORY_SEPARATOR, $filename), $is_full_path);
		
		// Setup File and Save	
		if( ! write_file($this->parse_filename($filename, $is_full_path), $this->parse_file_data($this->data, $var_name, $this->files_save_like)))
		{
			log_message('error', "Files::save() - File ($filename) failed to be saved.");
			return FALSE;
		}
		
		return TRUE;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Delete
	 * 
	 * You can pass this a specific file or a directory. If no file is passed and it 
	 * is a directory, it will delete all the files inside that directory.
	 * 
	 * @param	string	Filename - Include path if it is to be saved in subfolder
	 * @param	boolean	Delete current and sub directories. Only applies if filename is a directory
	 * @param	boolean	Whether the directory provided is the FULL directory
	 * @return	boolean
	 */
	public function delete($filename, $del_dir = FALSE, $is_full_path = FALSE)
	{
		$filename = ($is_full_path === FALSE) ? $this->files_path . $filename: $filename;
		
		if (is_dir($filename))
		{
			// TODO: Making the the increment (3rd param) a typecasted
			// integer of $del_dir would make it also delete the CURRENT directory
			// Just trying to decide whether or not that is the behavior I want
			// It would differ from CI's default behaviour for delete_files()
			//return delete_files($filename, $del_dir, (int) $del_dir);
			return delete_files($filename, $del_dir);
		}
		elseif (is_file($filename . $this->files_ext))
		{
			return unlink($filename . $this->files_ext);
		}
		
		return FALSE;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Create Directory
	 * 
	 * Ensures that the directory exists and will recursively create the requested
	 * directory for the file that is attempting to be created.
	 * 
	 * @since	1.0.0
	 * @param	array
	 * @param	boolean
	 * @return	boolean
	 */
	public function create_directory($dir, $is_full_path = FALSE)
	{
		// Recursively Create the Directory		
		if (count($dir) > 1)
		{
			// Check directory without the filename
			array_pop($dir);
			if ( ! is_dir(($is_full_path === FALSE ? $this->files_path: '') . implode(DIRECTORY_SEPARATOR, $dir)))
			{
				$path = array();
				// Recursively add directories
				foreach ($dir as $d)
				{
					$path[] = $d;
					$next_path = ($is_full_path === FALSE ? $this->files_path: '') . implode(DIRECTORY_SEPARATOR, $path);
					if ( ! is_dir($next_path) && ! mkdir($next_path))
					{
						log_message('error', "Files::save() - Failed to create directory " . implode(DIRECTORY_SEPARATOR, $path));
						return FALSE;
					}
				}
			}
		}
		
		return TRUE;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * List Files
	 * 
	 * This will return an array with relative pathnames included.
	 * It basically wraps get_filenames, except that this will return
	 * absolute paths when the second parameter is TRUE.
	 * 
	 * If file_info is not included, then the absolute path to the filename will be 
	 * the value, while the relative path will always be the key of the array
	 * 
	 * ['../relative/path' => 'absolute_path'] OR ['../relative/path' => [ FILE INFO ]]
	 * 
	 * NOTE: The relative path WILL be to whatever $this->files_path is defined as
	 * at the moment when this function is called. It is NOT relative to the FCPATH
	 * 
	 * @since	1.0.0
	 * @param	string	Optional Sub Directory
	 * @param	boolean	Get file information for each file.
	 * @param	boolean
	 * @return	array	FALSE on failure
	 */
	public function list_files($directory = '', $get_file_info = FALSE, $is_full_path = FALSE)
	{
		$directory = ($is_full_path === FALSE ? $this->files_path: '') . (empty($directory) ? $directory: $directory . DIRECTORY_SEPARATOR);
		$source_dir = rtrim(realpath($directory), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		$final = array();
		$files = get_filenames($directory, TRUE);		
		if (empty($files))
		{
			return FALSE;
		}
		
		// Setup final file format
		foreach ($files as $f)
		{
			$k = str_replace(array($source_dir, $this->files_path), array($directory, ''), $f);
			$final[$k] = ($get_file_info) ? get_file_info($f, $this->files_info_values): $f;
		}
		
		return $final;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Directory Map
	 * 
	 * Just a wrapper of the directory_map() helper function. This class adds the
	 * additional functionality of the configurable files_path. 
	 *
	 * Each folder name will be an array index, while its contained files will 
	 * be numerically indexed
	 * 
	 * @param	string
	 * @param	integer
	 * @param	boolean
	 * @param	boolean
	 * @return	array
	 */
	public function directory_map($directory = '', $directory_depth = 0, $hidden = FALSE, $is_full_path = FALSE)
	{
		$CI =& get_instance();
		$CI->load->helper('directory');
		$directory = ($is_full_path === FALSE ? $this->files_path: '') . $directory . DIRECTORY_SEPARATOR;
		return directory_map($directory, $directory_depth, $hidden);
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Parse CodeIgniter Data
	 * 
	 * The main purpose of this function is to then allow you to use CodeIgniter 
	 * specific functions throughout your application (e.g. lang('key'); )
	 * 
	 * @param	array	Config or Lang Arrays
	 * @return	boolean
	 */
	protected function parse_codeigniter_data($data)
	{
		if ( ! is_array($data)) return FALSE;
		$CI =& get_instance();
		
		// The Config class provides a public API to push on config values
		if ($this->files_save_like === 'config')
		{
			foreach ($data as $item => $value)
			{
				$CI->config->set_item($item, $value);
			}
		}
		// The Lang class does not provide a public API, but does expose the property
		elseif ($this->files_save_like === 'lang')
		{
			$CI->lang->language = array_merge($CI->lang->language, $data);
		}
		
		return TRUE;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Parse Filename
	 * 
	 * This assumes that no extension is included in the filename and does not
	 * currently have any safety checks in place.
	 * 
	 * @param	string	Filename
	 * @param	boolean
	 * @return	string
	 */
	protected function parse_filename($filename, $is_full_path)
	{
		return (($is_full_path === FALSE) ? $this->files_path . $filename: $filename) . $this->files_ext;
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Parse File Data
	 * 
	 * This function will parse the file data and object settings to output the 
	 * appropriate file string.
	 * 
	 * @since	1.0.0
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	protected function parse_file_data($data, $var_name, $files_save_like)
	{
		$file = '';
		$is_object = is_object($data);
		
		// In order to preserve the object as a whole, we serialize it and have the unserialize()
		// function wrapping the value in the file.
		// However, there is the potential for some major issues if the class of the object that you are unserializing
		// is not loaded in the current request. If you use this class to read the data from the file saved,
		// then it will check that the class is defined in the object. If it's not, none of the methods or properties
		// will be accessible and you'll get an error like the following
		// "The script tried to execute a method or access a property of an incomplete object"
		if ($is_object)
		{
			$data = serialize($data);
		}
		
		// Setup a PHP file
		if ($this->files_ext === '.php')
		{
			$file .= '<?php if ( ! defined("BASEPATH")) exit("No direct script access allowed");';
			$file .= "\n// @note - Do not edit this file directly. It is automatically generated. \n";
		
			if ($files_save_like === 'config')
			{
				$file .= '$config' . "['$var_name'] = ";
			}
			elseif ($files_save_like === 'lang')
			{
				if (is_array($data))
				{
					foreach ($data as $lang_key => $val)
					{
						if ( ! is_string($val))
						{
							log_message('error', 'Files::parse_file_data() - Lang type specified but array not setup properly. Values of array MUST be strings.');
							continue;
						}
						
						$file .= '$lang' . "['$lang_key'] = '$val';\n";
					}
				}
				elseif (is_string($data))
				{
					$file .= '$lang' . "['$var_name'] = '$data';\n";
				}			
			}
			else
			{
				$file .= "$$var_name = ";
			}
					
			// Finalize File Contents and append a semicolon to PHP data
			if ($files_save_like !== 'lang')
			{
				$file .= $is_object ? 'unserialize(': '';
				$file .= var_export($data, TRUE) . ($is_object ? ');': ';');
			}
		}
		// If not PHP, just save entire string as file
		elseif (is_string($data))
		{
			$file = $data;
		}
		
		$this->data = NULL; // Reset data to prevent bleed over
		return $file;
	}
	
}