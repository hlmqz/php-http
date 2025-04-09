<?php

namespace Hlmqz\Http;

class Responded
{
	public float $requestDuration
	{
		get => $this->curlInfo['total_time_us']/1000000 ?? 0;
	}

	function __construct(
		private(set) int $httpCode = 0,
		private(set) string $contentType = '',
		private(set) array $urlInfo = [],
		private(set) string $url = '',
		private(set) string $method = '',
		private(set) array $headers = [],
		private(set) object|array|string|null $content = null,
		private(set) mixed $error = null,
		private(set) object|array $dataSended = [],
		private(set) object|array $curlInfo = [],
	)
	{}
}
