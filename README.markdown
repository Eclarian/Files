# Files Package

This spark makes it easy to save any type of file but is specifically tailored to save and read PHP files. This spark handles formatting whatever data type you pass to it when saving as a PHP file. It also provides simple methods for saving CodeIgniter language and configuration files.

To illustrate the benefits of saving any type of data imagine you have a System_settings class that you use to store application specific information. This makes it easy to save and read the stored object at a later time.

	// Example Class
	class System_settings {
		
		public $site_name = "";
		
		public function __construct($name = '')
		{
			$this->site_name = $name;
		}
		
		public function get_site_name()
		{
			return $this->site_name;
		}
	}

	$this->files->data(new System_settings("My Site"))->save('system_settings', 'settings');
	$sys = $this->files->read('system_settings', 'settings');
	echo $sys->get_site_name(); // Outputs "My Site"

This package supports basically any type of variable found in PHP.

	// Other basic examples of using different variable types
	$this->files->data(1.001)->save('float', 'settings');
	$this->files->data(1231)->save('integer', 'settings');
	$this->files->data("Example")->save('string', 'settings');
	$this->files->data(array())->save('array', 'settings');
	$this->files->data(FALSE)->save('boolean', 'settings');
	

## Public API

Full Path is relative to the FCPATH (the index.php file). So if you want to save something below the index.php file on your server just have a line like the following **$this->files->save("../parallel_to_public_html/file", 'parallel', TRUE);**

### $this->files->setup([ array $settings]);
Allows you to override the default configuration by passing an array where the keys match the configuration settings.

### $this->files->ext([string $e = ".php"]);
Change the extension of the file you are attempting to save or read. By default this is '.php' but you can change it to anything.

### $this->files->like([string $type = "config"]);
If the extension is PHP it will save or read the data like the specified CodeIgniter file. Available options are "config", "lang", or "" for none

### $this->files->data( mixed $data);
This must be called before save(), or no data will be saved to the file. Data will be reset everytime save() or read() is called.
	
### $this->files->save( string $filename, [, string $key_name = 'files_data' [, bool $is_full_path = FALSE]]);

	$filename = 'path/filename'; // EXTENSION NOT NEEDED
	$this->files->save($filename, 'key_name');

### $this->files->read( string $filename [, string $key_name = 'files_data' [, bool $is_full_path = FALSE]]);
This will load the file and return the requested variable if available

	$cache = $this->files->read('path/filename', 'key_name');
	
### $this->files->delete( string $filename [, bool $is_full_path = FALSE]);
You can pass this a specific file or a directory. If no file is passed and it is a directory, it will delete all the files inside that directory.

### $this->files->list_files([ string $directory [, bool $get_file_info = FALSE [, bool $is_full_path = FALSE]]]);
This will list everything that is stored in app cache for the directory provided. If none is provided, then it will list for all found in the core app cache directory.

### $this->files->directory_map([ string $directory = '' [, $directory_depth = 0 [, $hidden = FALSE [, $is_full_path = FALSE]]]])
This function wraps CI's directory_map helper and just gives the added benefit of using the configured 'files_path'.

### $this->files->create_directory( string $directory [, $is_full_path = FALSE]);
This allows you to create a directory without creating any files within it. 
NOTE: This will be called automatically from within save(), so you ONLY need to use this if you want to create an empty directory.


## Configuration

### $config['files_path'] = APPPATH . 'cache/';
By default the app cache stores all the new files within the APPPATH . 'cache/' directory.

### $config['files_save_like'] = 'config';
Whether or not the files should be stored like a CodeIgniter file. Available options are "config", "lang" or "".
NOTE: If you are saving a language file, the data type must either be a string or an array.

### $config['files_ext'] = '.php';
The default extension used to save and read files.


## Change Log

**1.0.1**
 * Fixed bug in the read() method where the cache would return a partial piece of strings (if the value was a string) on occasions when it would read it as isset().

**1.0.0**
 * Released