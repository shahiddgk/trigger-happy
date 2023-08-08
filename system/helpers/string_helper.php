<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019 - 2022, CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @copyright	Copyright (c) 2019 - 2022, CodeIgniter Foundation (https://codeigniter.com/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter String Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/userguide3/helpers/string_helper.html
 */

// ------------------------------------------------------------------------

if ( ! function_exists('trim_slashes'))
{
	/**
	 * Trim Slashes
	 *
	 * Removes any leading/trailing slashes from a string:
	 *
	 * /this/that/theother/
	 *
	 * becomes:
	 *
	 * this/that/theother
	 *
	 * @todo	Remove in version 3.1+.
	 * @deprecated	3.0.0	This is just an alias for PHP's native trim()
	 *
	 * @param	string
	 * @return	string
	 */
	function trim_slashes($str)
	{
		return trim($str, '/');
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('strip_slashes'))
{
	/**
	 * Strip Slashes
	 *
	 * Removes slashes contained in a string or in an array
	 *
	 * @param	mixed	string or array
	 * @return	mixed	string or array
	 */
	function strip_slashes($str)
	{
		if ( ! is_array($str))
		{
			return stripslashes($str);
		}

		foreach ($str as $key => $val)
		{
			$str[$key] = strip_slashes($val);
		}

		return $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('strip_quotes'))
{
	/**
	 * Strip Quotes
	 *
	 * Removes single and double quotes from a string
	 *
	 * @param	string
	 * @return	string
	 */
	function strip_quotes($str)
	{
		return str_replace(array('"', "'"), '', $str);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('quotes_to_entities'))
{
	/**
	 * Quotes to Entities
	 *
	 * Converts single and double quotes to entities
	 *
	 * @param	string
	 * @return	string
	 */
	function quotes_to_entities($str)
	{
		return str_replace(array("\'","\"","'",'"'), array("&#39;","&quot;","&#39;","&quot;"), $str);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('reduce_double_slashes'))
{
	/**
	 * Reduce Double Slashes
	 *
	 * Converts double slashes in a string to a single slash,
	 * except those found in http://
	 *
	 * http://www.some-site.com//index.php
	 *
	 * becomes:
	 *
	 * http://www.some-site.com/index.php
	 *
	 * @param	string
	 * @return	string
	 */
	function reduce_double_slashes($str)
	{
		return preg_replace('#(^|[^:])//+#', '\\1/', $str);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('reduce_multiples'))
{
	/**
	 * Reduce Multiples
	 *
	 * Reduces multiple instances of a particular character.  Example:
	 *
	 * Fred, Bill,, Joe, Jimmy
	 *
	 * becomes:
	 *
	 * Fred, Bill, Joe, Jimmy
	 *
	 * @param	string
	 * @param	string	the character you wish to reduce
	 * @param	bool	TRUE/FALSE - whether to trim the character from the beginning/end
	 * @return	string
	 */
	function reduce_multiples($str, $character = ',', $trim = FALSE)
	{
		$str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);
		return ($trim === TRUE) ? trim($str, $character) : $str;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('random_string'))
{
	/**
	 * Create a "Random" String
	 *
	 * @param	string	type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
	 * @param	int	number of characters
	 * @return	string
	 */
	function random_string($type = 'alnum', $len = 8)
	{
		switch ($type)
		{
			case 'basic':
				return mt_rand();
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric':
						$pool = '0123456789';
						break;
					case 'nozero':
						$pool = '123456789';
						break;
				}
				return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
			case 'unique': // todo: remove in 3.1+
			case 'md5':
				return md5(uniqid(mt_rand()));
			case 'encrypt': // todo: remove in 3.1+
			case 'sha1':
				return sha1(uniqid(mt_rand(), TRUE));
		}
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('valid_timezone'))
{
	/**
	 * Create a "Random" String
	 *
	 * @param	string	type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
	 * @param	int	number of characters
	 * @return	string
	 */
	function valid_timezone()
	{
		return  array(
            "European Central Time (GMT+1:00)" => "Europe/Amsterdam",
            "Eastern European Time (GMT+2:00)" => "Europe/Athens",
            "Egypt Standard Time (GMT+2:00)" => "Africa/Cairo",
            "Eastern African Time (GMT+3:00)" => "Africa/Nairobi",
            "Middle East Time (GMT+3:30)" => "Asia/Tehran",
            "Near East Time (GMT+4:00)" => "Asia/Dubai",
            "Pakistan Lahore Time (GMT+5:00)" => "Asia/Karachi",
            "India Standard Time (GMT+5:30)" => "Asia/Kolkata",
            "Bangladesh Standard Time (GMT+6:00)" => "Asia/Dhaka",
            "Vietnam Standard Time (GMT+7:00)" => "Asia/Bangkok",
            "China Taiwan Time (GMT+8:00)" => "Asia/Taipei",
            "Japan Standard Time (GMT+9:00)" => "Asia/Tokyo",
            "Australia Central Time (GMT+9:30)" => "Australia/Darwin",
            "Australia Eastern Time (GMT+10:00)" => "Australia/Sydney",
            "Solomon Standard Time (GMT+11:00)" => "Pacific/Guadalcanal",
            "New Zealand Standard Time (GMT+12:00)" => "Pacific/Auckland",
            "Midway Islands Time (GMT-11:00)" => "Pacific/Midway",
            "Hawaii Standard Time (GMT-10:00)" => "Pacific/Honolulu",
            "Alaska Standard Time (GMT-9:00)" => "America/Anchorage",
            "Yukon Standard Time (GMT-8:00)" => "America/Whitehorse",
            "Alaska-Hawaii Standard Time (GMT-9:00)" => "America/Adak",
            "Pacific Standard Time (GMT-8:00)" => "America/Los_Angeles",
            "Phoenix Standard Time (GMT-7:00)" => "America/Phoenix",
            "Central Standard Time (GMT-6:00)" => "America/Chicago",
            "Mountain Standard Time (GMT-7:00)" => "America/Denver",
            "Eastern Standard Time (GMT-5:00)" => "America/New_York",
            "Indiana Eastern Standard Time (GMT-5:00)" => "America/Indiana/Indianapolis",
            "Puerto Rico and US Virgin Islands Time (GMT-4:00)" => "America/Puerto_Rico",
            "Canada Newfoundland Time (GMT-3:30)" => "America/St_Johns",
            "Argentina Standard Time (GMT-3:00)" => "America/Argentina/Buenos_Aires",
            "Brazil Eastern Time (GMT-3:00)" => "America/Sao_Paulo",
            "Central African Time (GMT-1:00)" => "Africa/Luanda"
        );
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('increment_string'))
{
	/**
	 * Add's _1 to a string or increment the ending number to allow _2, _3, etc
	 *
	 * @param	string	required
	 * @param	string	What should the duplicate number be appended with
	 * @param	string	Which number should be used for the first dupe increment
	 * @return	string
	 */
	function increment_string($str, $separator = '_', $first = 1)
	{
		preg_match('/(.+)'.preg_quote($separator, '/').'([0-9]+)$/', $str, $match);
		return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('alternator'))
{
	/**
	 * Alternator
	 *
	 * Allows strings to be alternated. See docs...
	 *
	 * @param	string (as many parameters as needed)
	 * @return	string
	 */
	function alternator()
	{
		static $i;

		if (func_num_args() === 0)
		{
			$i = 0;
			return '';
		}

		$args = func_get_args();
		return $args[($i++ % count($args))];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('repeater'))
{
	/**
	 * Repeater function
	 *
	 * @todo	Remove in version 3.1+.
	 * @deprecated	3.0.0	This is just an alias for PHP's native str_repeat()
	 *
	 * @param	string	$data	String to repeat
	 * @param	int	$num	Number of repeats
	 * @return	string
	 */
	function repeater($data, $num = 1)
	{
		return ($num > 0) ? str_repeat($data, $num) : '';
	}
}
