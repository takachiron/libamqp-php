<?php

namespace libamqp;

use \InvalidArgumentException;

require_once('Value.php');

/**
 * Represents an AMQP Primitive Type timestamp
 *
 * @category Networking
 * @package libamqp
 * @author Raphael Cohn <raphael.cohn@stormmq.com>
 * @author Eamon Walshe <eamon.walshe@stormmq.com>
 * @copyright 2012 Raphael Cohn and Eamon Walshe
 * @license http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 * @version Release: @package_version@
 * @todo: Can not be supported on 32-bit PHP
 */
class timestamp implements Value
{
	public $value;

	/**
	 * @param int $value
	 */
	public function __construct($value)
	{
		if (!is_int($value))
		{
			throw new InvalidArgumentException("$value is not an int");
		}
		if ($value < -9223372036854775808 || $value > 9223372036854775807)
		{
			throw new InvalidArgumentException("$value is not an int (-9223372036854775808 to 9223372036854775807)");
		}
		$this->value = $value;
	}

	/**
	 * @static
	 * @param int $value
	 * @return char
	 */
	public static function instance_from_php_value($value)
	{
		return new char($value);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf("%s(%u)", __CLASS__, $this->value);
	}
}

?>
