<?php

use PHPUnit\Framework\TestCase;
use Hlmqz\Http\Requester;
use Hlmqz\Http\Responded;

class RequestTest extends TestCase
{
	public function testRequester()
	{
		$requester = new Requester('https://jsonplaceholder.typicode.com/posts/1');
		$responded = $requester->send();

		$this->assertTrue(is_a($responded, Responded::class));
		$this->assertEquals($responded->httpCode, 200);
	}
}
