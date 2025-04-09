<?php

use PHPUnit\Framework\TestCase;
use Hlmqz\Http\Requester;
use Hlmqz\Http\Responded;

class MultipleRequestTest extends TestCase
{
	public function testRequester()
	{
		$requester = new Requester();
		$tries = range(0,9);

		$url = 'https://httpbin.org/get';

		$responded = [];

		foreach ($tries as $try)
		{
			$requester->addMultiple(new Requester($url));
		}

		$responded = $requester->sendMultiple();

		$this->assertIsArray($responded);
	}
}
