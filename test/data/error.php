<?php

return [
	[
		"date" => new DateTime("28-Jul-2015 11:21:22 America/Detroit"),
		"type" => "Notice",
		"message" => "Hello world",
		"stack" => [
			[
				"file" => "foo.php",
				"line" => 93,
			],
			[
				"file" => "bar.php",
				"line" => 0,
			],
			[
				"file" => "bar.php",
				"line" => 47,
			],
			[
				"file" => "bar.php",
				"line" => 2,
			],
			[
				"file" => "bar.php",
				"line" => 116,
			],
			[
				"file" => "bar.php",
				"line" => 54,
			],
			[
				"file" => "bar.php",
				"line" => 54,
			],
			[
				"file" => "bar.php",
				"line" => 93,
			],
		],
	],
	[
		"date" => new DateTime("28-Jul-2015 11:21:22 America/Detroit"),
		"type" => "Fatal error",
		"message" => "Uncaught exception 'Exception' with message 'Hello world'",
		"stack" => [
			[
				"file" => "foo.php",
				"line" => 94,
			],
			[
				"file" => "bar.php",
				"line" => 54,
			],
			[
				"file" => "bar.php",
				"line" => 116,
			],
			[
				"file" => "bar.php",
				"line" => 2,
			],
			[
				"file" => "bar.php",
				"line" => 47,
			],
		],
	],
	[
		"date" => null,
		"type" => null,
		"message" => "Bad error message",
		"stack" => [],
	]
];
