<?php

use PHPUnit\Framework\TestCase;
use Hlmqz\Http\Requester;
use Hlmqz\Http\Responded;

class RequestTest extends TestCase
{
	public function testRequester()
	{
		$requester = new Requester('https://httpbin.org/get');
		$responded = $requester->send();

		$this->assertTrue(is_a($responded, Responded::class));
		$this->assertEquals($responded->httpCode, 200);
	}
}
