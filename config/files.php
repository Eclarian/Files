<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Files Path
|--------------------------------------------------------------------------
|
|  !! TRAILING SLASH IS REQUIRED !!
*/
$config['files_path'] = APPPATH . 'cache/';

/*
|--------------------------------------------------------------------------
| Files Save Like
|--------------------------------------------------------------------------
|  
|  This will only try to save the file as the type specified
|  if the extension is ".php"
|  
|  Available Options: "config" or "lang" 
|  
|  NOTE: Assuming that CI does not suddenly change their classes
|   properties to protected, this package will attempt to push
|   configuration and language onto their respective object properties
*/
$config['files_save_like'] = 'config';

/*
|--------------------------------------------------------------------------
| Default Extension
|--------------------------------------------------------------------------
|
|  This defaults to PHP but can be adjusted either here in the config
|  or on the fly with the ext() method
*/
$config['files_ext'] = '.php';

/*
|--------------------------------------------------------------------------
| Default Extension
|--------------------------------------------------------------------------
|
|  These are any file extensions that should be included into current execution
|  (e.g. evaluated instead of read) You probably will never have to add any
|  additional extensions besides PHP unless you are integrating with another
|  system that may use ".inc" files or something like that
*/
$config['files_to_include'] = array('.php');