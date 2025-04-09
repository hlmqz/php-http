<?php
/**
 * @author hemerson Marquez <hlmarquezm@gmail.com>
 */

namespace Hlmqz\Http;

class Requester
{
	private(set) array $methods = [
		'GET',
		'HEAD',
		'POST',
		'PUT',
		'DELETE',
		'OPTIONS',
		'PATCH',
		// 'CONNECT',
		// 'TRACE',
		// 'QUERY',
	];

	private(set) array $types = [
		'json' => 'application/json',
		'form' => 'multipart/form-data',
		'urlencoded' => 'application/x-www-form-urlencoded',
	];

	protected(set) string $id;
	protected(set) Responded $responded;
	protected(set) object|array $last = [];
	protected ?object $curl = null;
	protected array $urlinfo = [];
	protected array $multiple = [];
	protected mixed $response = null;

//-----------------------------------------------------------------------------------

	public string $method = 'GET'
	{
		set(string $value)
		{
			$method = trim(strtoupper($value));

			if(!in_array($method, $this->methods))
				throw new \UnexpectedValueException(
					"method ({$value}) is not a valid value, it must be one of: ".implode(', ', $this->methods)
				);

			$this->method = $method;
		}
	}

//-----------------------------------------------------------------------------------

	public string $type = 'json'
	{
		set(string $value)
		{
			$type = trim(strtolower($value));
			$types = array_keys($this->types);

			if(!in_array($type, $types))
				throw new \UnexpectedValueException(
					"type ({$value}) is not a valid value, it must be one of: ".implode(', ', $types)
				);

			$this->type = $type;
		}
	}

//-----------------------------------------------------------------------------------

	public int $timeLimit = 30
	{
		set(int $value)
		{
			if($value < 0)
				throw new \UnexpectedValueException(
					"timeLimit ({$value}) must be greater than or equal to zero"
				);

			$this->timeLimit = $value;
		}
	}

//-----------------------------------------------------------------------------------

	public string $url = ''
	{
		set(string $value)
		{
			$urlinfo = [
				'scheme' => 'http',
				'host' => '',
				'colon' => '',
				'port' => '',
				'path' => '/',
				'query' => '',
			];

			$this->urlinfo = array_merge($urlinfo, parse_url($value));

			if(isset($this->urlinfo['query']))
				parse_str($this->urlinfo['query'], $this->urlinfo['query_array']);

			$this->urlinfo['colon'] = ($this->urlinfo['port'] ? ':' : '');

			$this->url = $value;
		}
	}

//===================================================================================

	function __construct(
			string $url='',
			string $method = 'GET',
			public object|array $data = [],
			public array $headers = [],
		)
	{
		$this->id = bin2hex(random_bytes(16));
		$responded = new Responded();
		$this->method = $method;
		$this->url = $url;
	}

//===================================================================================
// *************************** PRIVATE METHODS ***************************
//===================================================================================

	private function splitHeader(string $head): Array
	{
		$parts = explode(':', $head, 2);
		if(count($parts) < 2) return [];

		return [
			'header' => trim($parts[0]),
			'value' => trim($parts[1]),
		];

	}

//===================================================================================

	private function prepare(): Array
	{

		$heads = [];

		$heads['content-type'] = [
			'header' => 'Content-Type',
			'value' => $this->types[$this->type],
		];

		foreach ($this->headers as $k => $v)
		{
			if(!(is_array($v) || is_object($v)))
			{
				if(is_numeric($k))
				{
					$tmphead = $this->splitHeader($v);

					if($tmphead)
						$heads[strtolower(trim($tmphead['header']))] = $tmphead;
				}
				else
				{
					$heads[strtolower($k)] = [
						'header' => trim($k),
						'value' => $v,
					];
				}
			}
		}

		$headers = [];
		foreach ($heads as $head)
		{
			$headers[] = "{$head['header']}:{$head['value']}";
		}

		$url = "{$this->urlinfo['scheme']}://{$this->urlinfo['host']}";
		$url .= "{$this->urlinfo['colon']}{$this->urlinfo['port']}{$this->urlinfo['path']}";

		$query = '';
		$data = '';

		if(in_array($this->method, ['POST','PUT','PATCH',]))
		{

			if(is_array($this->data))
			{
				switch ($this->type)
				{
					//-----------------------------------------------------------------
					case 'json':
							$data = json_encode($this->data, JSON_UNESCAPED_SLASHES);
						break;
					//-----------------------------------------------------------------
					case 'urlencoded':
							$data = http_build_query($this->data);
						break;
					//-----------------------------------------------------------------
					case 'form':
					default:
							$data = $this->data;
						break;
				}
			}
			else
				$data = $this->data;
		}
		else
		{
			if(is_array($this->data))
			{
				$urlquery = array_merge($this->urlinfo['query_array'], $this->data);
				$query = http_build_query($urlquery);
			}
		}

		return [
			'url' => $url,
			'urlinfo' => $this->urlinfo,
			'fullurl' => $url.($query ? "?{$query}" : ''),
			'method' => $this->method,
			'rawdata' => $this->data,
			'data' => $data,
			'query' => $query,
			'headers' => $headers,
		];

	}

//===================================================================================

protected function generateCurl()
{
	$this->last = $this->prepare();
	$this->curl = curl_init($this->last['fullurl']);

	curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->last['method']);
	curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->last['headers']);
	curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeLimit);
	curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($this->curl, CURLOPT_HEADER, true);

	if(in_array($this->last['method'], ['POST','PUT','PATCH',]))
	{
		curl_setopt($this->curl, CURLOPT_POST, true);

		if($this->last['data'])
		{
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->last['data']);
		}
	}

	return $this->curl;
}

//===================================================================================

protected function processCurlResponse($toArray = false): ?Responded
{
	if(!$this->curl)
		return null;

	$headers = [];
	$content = null;

	if($this->response === false)
	{
		$this->last['error'] = curl_error($this->curl);
	}
	else
	{
		$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);

		$rawheaders = explode("\n", trim(substr($this->response, 0, $header_size)));
		$content = trim(substr($this->response, $header_size));

		foreach ($rawheaders as $line)
		{
			$head = $this->splitHeader($line);
			if($head)
				$headers[strtolower($head['header'])] = $head['value'];
		}
	}

	$this->last['curl_info'] = curl_getinfo($this->curl);

	$this->last['content'] = $content;
	$this->last['content_json'] = $content ? json_decode($content, $toArray) : [];

	curl_reset($this->curl);
	curl_close($this->curl);
	$this->curl = null;

	$this->responded = new Responded(
		httpCode: $this->last['curl_info']['http_code'] ?? 0,
		contentType: $this->last['curl_info']['content_type'] ?? '',
		url: $this->last['fullurl'],
		urlInfo: $this->last['urlinfo'],
		method: $this->last['method'],
		headers: $headers,
		content: $this->last['content_json'] ?? $this->last['content'] ?? [],
		error: $this->last['error'] ?? null,
		dataSended: $this->last['rawdata'],
		curlInfo: $this->last['curl_info'],
	);

	return $this->responded;
}

//===================================================================================
// *************************** PUBLIC METHODS ****************************
//===================================================================================

public function addMultiple(Requester $requester): string
{
	$this->multiple[$requester->id] = $requester;
	return $requester->id;
}

//===================================================================================

public function sendMultiple($toArray = false): array
{
	$multiHandler = curl_multi_init();

	foreach ($this->multiple as $requester)
		curl_multi_add_handle($multiHandler, $requester->generateCurl());

	do {

		$status = curl_multi_exec($multiHandler, $running);

		if ($running)
			curl_multi_select($multiHandler);

	} while ($running && $status === CURLM_OK);

	$responded = [];

	foreach ($this->multiple as $requester)
	{
		$requester->response = curl_multi_getcontent($requester->curl);

		curl_multi_remove_handle($multiHandler, $requester->curl);
		$responded[$requester->id] = $requester->processCurlResponse($toArray);
	}

	return $responded;
}

//===================================================================================

	public function send($toArray = false): Responded
	{
		if(!$this->curl)
			$this->generateCurl();

		$this->response = curl_exec($this->curl);

		return $this->processCurlResponse($toArray);
	}

//===================================================================================
}
