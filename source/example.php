<?php

require_once('libamqp/libamqp.php');

use \libamqp\sending_link;
use \libamqp\ushort, \libamqp\boolean, \libamqp\string, \libamqp\binary, \libamqp\timestamp, \libamqp\ubyte, \libamqp\uint, \libamqp\byte, \libamqp\char;
use \libamqp\header, \libamqp\delivery_annotations, \libamqp\message_annotations, \libamqp\properties, \libamqp\application_properties;
use \libamqp\data, \libamqp\amqp_value, \libamqp\amqp_sequence;
use \libamqp\footer, \libamqp\delivery_state, \libamqp\outcome, \libamqp\received;

/*
	1 Creates a connection shared globally if none exists
	2 Creates a session shared globally if none exists
	3 Creates a new link suitable for a synchronous send
		- This draft API does not address exactly-once messaging, etc, yet
		- This draft API does not address any error handling, eg link-redirect
		- Link recovery is not supported (and partial message recovery would be exceedingly hard to do)
	4 Sends a data(message)

	Sending messages is a blocking operation; any invoked callbacks, eg for exactly-once messaging, happen within
	the send() function. This avoids extremely nasty problems involving PHP's garbage collection and page lifetimes.

	Obviously, this is sub-optimal, but then again, PHP has no real concept of asynchronous programming or threading.
*/
$link = new sending_link("link-name", NULL);
$link->send("message");

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an amqp-value(null)
*/
$link->send(NULL);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an amqp-value(boolean(TRUE))
*/
$link->send(TRUE);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an amqp-value(long(56789))
*/
$link->send(56789);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an amqp-value(double(14.56))
*/
$link->send(14.56);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an amqp-value(ushort(456))
*/
$link->send(new ushort(456));

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an arbitary amqp-value, in this case, amqp-value(ushort(456))
*/
$link->send(new amqp_value(new ushort(456)));

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends a data(message)
*/
$binary_data = "message";
$link->send(new data($binary_data));

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends an amqp-sequence(boolean(TRUE), null(), string("hello"))
*/
$amqp_sequence = new amqp_sequence();
$amqp_sequence[0] = boolean::TRUE();
$amqp_sequence[2] = new string("hello");
$link->send($amqp_sequence);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends multiple data sections
*/
$section0 = new data("hello");
$section1 = new data("world");
$amqp_data_sections = array
(
	$section0,
	$section1
);
$link->send($amqp_data_sections);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends multiple amqp-sequence sections
*/
$section0 = new amqp_sequence();
$section0[0] = boolean::TRUE();
$section0[2] = new string("hello");
$section1 = new amqp_sequence();
$section1[0] = boolean::FALSE();
$section1[2] = new string("world");
$amqp_data_sections = array
(
	$section0,
	$section1
);
$link->send($amqp_data_sections);

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends header and binary message
*/
$link->send("messsage", new header(true, 6));

/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends header, delivery-annotations, message-annotations, properties, application-properties and binary message
*/
$link->send
(
	"messsage",
	new header
	(
		FALSE,
		3,
		NULL,
		TRUE,
		2
	),
	new delivery_annotations(array
	(
		"x-opt-delivery-something" => boolean::TRUE(),
		"x-opt-delivery-whatever"  => char::instance_from_php_value(56789)
	)),
	new message_annotations(array
	(
		"x-opt-message-somesuch" => byte::instance_from_php_value(-1),
		"x-opt-message-somesuch" => ubyte::instance_from_php_value(15)
	)),
	new properties
	(
		"message-id-56",
		"somebinarydataforuserid",
		"to",
		"subject",
		"reply-to",
		binary::instance_from_php_value("correlation-id"),
		"text/plain;charset=utf-8",
		NULL,
		123456,
		new timestamp(123456),
		"group-id",
		90,
		string::instance_from_php_value("reply-to-group-id")
	),
	new application_properties(array
	(
		"my-key-1" => boolean::TRUE(),
		"my-key-2" => boolean::FALSE()
	))
);


/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends binary message and uses an array of callbacks to create the footer
*/
$footer_callbacks = array
(
	function(footer &$footer, $encoded_data_binary_string)
	{
		$footer->set('x-hash-sha1', new binary(sha1($encoded_data_binary_string, TRUE)));
		$footer->set('x-hash-md5', new binary(md5($encoded_data_binary_string, TRUE)));
	},
	function(footer &$footer, $encoded_data_binary_string)
	{
		$key = 'fsfsfsfsfs342534534';
		$footer->set('x-hmac-sha256', new binary(hash_hmac('sha256', $encoded_data_binary_string, $key, TRUE)));
	},
);
$link->send("message", NULL, NULL, NULL, NULL, NULL, $footer_callbacks);


/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends binary message using at-least-once messaging
*/
$delivery_state_callback = function(delivery_state &$delivery_state)
{
	if (!($delivery_state instanceof outcome))
	{
		return;
	}
	$outcome = $delivery_state;
	switch(get_class($outcome))
	{
		case 'libamqp\accepted':

			break;

		case 'libamqp\rejected':

			break;

		case 'libamqp\released':

			break;

		case 'libamqp\modified':

			break;

		default:
			throw new BadFunctionCallException("Unknown outcome class $delivery_state");
	}
};
$link->send("message", NULL, NULL, NULL, NULL, NULL, array(), constant('LIBAMQP_DELIVERY_MODE_AT_LEAST_ONCE'), $delivery_state_callback);


/*
	1 (Restablishes connection, session or link if necessary)
	2 Sends binary message using exactly-once messaging, and mirrors the receiver's outcome (for simplicity)
*/
$delivery_state_callback = function(delivery_state &$delivery_state)
{
	if (!($delivery_state instanceof outcome))
	{
		return NULL;
	}
	$outcome = $delivery_state;
	switch(get_class($outcome))
	{
		case 'libamqp\accepted':
			return $delivery_state;

		case 'libamqp\rejected':
			return $delivery_state;

		case 'libamqp\released':
			return $delivery_state;

		case 'libamqp\modified':
			return $delivery_state;

		default:
			throw new BadFunctionCallException("Unknown outcome class $delivery_state");
	}
};
$link->send("message", NULL, NULL, NULL, NULL, NULL, array(), constant('LIBAMQP_DELIVERY_MODE_EXACTLY_ONCE'), $delivery_state_callback);


?>
