<?php namespace Teil\Lib;



/**
* Validate license key for module
*/
class Security 
{

	/**
	 * This is the url where we have our server script
	 *
	 * @const string
	 */
	const SERVER_URL = 'http://dev.website-builder.ru/';
	
	
	function __construct() {}


	/**
	 * Validate license key to:
	 * - domain
	 * - module code
	 *
	 * and return result of operation.
	 *
	 * [valid] => false
	 * OR
	 * [valid] => true
	 * [... license data ...] => []
	 *
	 * @return mixed
	 */
	public function validate($domain, $module_code)
	{
		$result = $this->query(
			$this->getLicenseCode($module_code),
			$domain,
			$module_code
		);

		// Return license information
		return $result;
	}


	/**
	 * Perform query to our license server
	 *
	 * @return mixed
	 */
	private function query($license_key, $domain, $module_code)
	{
		$ch = curl_init(self::SERVER_URL);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_POST, true);

		// Set data to send via POST method
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'key' => $license_key,
			'domain' => $domain,
			'module_code' => $module_code
		));

		// Execute and decode from json
		$result = curl_exec($ch);
		$result = json_decode($result, true);

		return $result;
	}


	/**
	 * Get license key from license.dat file
	 *
	 * @return string
	 */
	private function getLicenseCode($module_code)
	{
		$license_key_path = DIR_TEIL_MODULES . $module_code . '/resources/license.dat';

		if (file_exists($license_key_path))
		{
			$licenseCode = file_get_contents($license_key_path);
		}
		else
		{
			throw new \Exception("License file not found!");
		}

		return $this->clean($licenseCode);
	}


	/**
	 * Clean/unescape string
	 *
	 * @return string
	 */
	private function clean($value)
	{
		// Clean the input of SQL Injection
		if (get_magic_quotes_gpc())
		{
			// Remove the slashes that magic_quotes added
			$value = stripslashes($value);
		}
		
		if (!is_numeric($value))
		{
			// Escape and ' or " to remove SQL Injections
			$value = $GLOBALS['app']->make('registry')->get('db')->escape($value);
		}

		return $value;
	}


}