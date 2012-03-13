<?php

namespace libamqp;

use \BadMethodCallException;

require_once('outcome.php');

/**
 * released
 *
 * @category Networking
 * @package libamqp
 * @author Raphael Cohn <raphael.cohn@stormmq.com>
 * @author Eamon Walshe <eamon.walshe@stormmq.com>
 * @copyright 2012 Raphael Cohn and Eamon Walshe
 * @license http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 * @version Release: @package_version@
 */
class released extends outcome
{
	protected static $descriptor_name;
	protected static $descriptor_code;
	protected static $listMappings = array
	(
	);

	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Yes, this uses fall-through, but does so to make sure trailing null suppression is possible
		$numberOfArgumentsSpecified = func_num_args();
		switch ($numberOfArgumentsSpecified)
		{
			case 0:
				break;

			default:
				throw new BadMethodCallException("too many function arguments");
		}
	}
}

require_once('symbol.php');
require_once('ulong.php');

released::init(new symbol("amqp:released:list"), ulong::instance_from_domain(0x00000000, 0x00000026));

?>
