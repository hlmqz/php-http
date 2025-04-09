<h1 align="center">
	<a href="https://hlmqz.github.io/map-grid-density-demo/">
		<img height="240" src="https://github.com/hlmqz/php-http/blob/master/php-84-http-requests.png"/>
	</a>
</h1>

# PHP-HTTP Requests

Este Paquete es una implementación sencilla y controlada de realizar Peticiones HTTP
desde PHP sin dependencias innecesarias.

### Requerimientos

- PHP 8.4 o Superior

## Implementación

### Inicialización

La clase es dinámica en su creación para facilitar el proceso,
el constructor puede recibir 4 parámetros opcionales,
se recomienda pasarlos de manera nombrada, si el orden no
es lo que busca, los parámetros y el orden es:

- `string $url`: la url a la cual se realiza la petrción.
- `string $method`: es el método a implementaar la petción.
- `object|array $data`: Son los datos  enviar en la petición, si los requiere.
- `array $headers`: son las cabeceras para agregar a la petción, de ser necesario. 


```php

use Hlmqz\Http\Requester;

// se puede especificando solo la URL, el método es por defecto GET
$requester = new Requester('https://httpbin.org/get');

// se puede especificando solo la URL y el método
$requester = new Requester('https://httpbin.org/get', 'GET');

// con pámetros nombrados
$requester = new Requester(
	url: 'https://httpbin.org/get',
	method: 'GET',
	data: [],
	headers: [],
);

// se puede sin especicar en la construcción, pero si asignando varaibles url y method
$requester = new Requester();
$requester->url = 'https://httpbin.org/get';
$requester->method = 'GET';
$requester->data = [ ... ];
$requester->headers = [ ... ];

```

las caracteristicas de configuración que no son obligatorias pero están disponibles son:


```php

// string id: tiene un id UUID compatible para identificar el objeto Requester. 
$requester->id;

// array methods: no se puede escribir, muestra los métodos disponibles.
$requester->methods;

// string method: establece el método a usar, por defecto 'GET'.
$requester->method = 'GET';

// array types: no se puede escribir, muestra los tipos de contenido, un array.
$requester->types;

// string type: establece el tipo de contendio a enviar, los indices de types
// son los válidos, por defecto 'json'.
$requester->type = 'json';

// int timeLimit: establece el tiempo límite para TIMEOUT de CURL, por defecto 30.
$requester->timeLimit = 30;

// string url: es la URL a la cual se le realizará la petición, por defecto es vacio.
$requester->url = '';

// array last: tiene los datos preparados de la petición anterior.
$requester->last;

// Responded responded: tiene un objecto Responded, los datos recibidos de la última petición.
$requester->responded;

```

### Envío de la petición

Para realizar la petición, solo es llamar al método `send` que tiene un parámetro no obligatorio `toArray`
que definie si el contenido será devuelto como Array, por defecto es `false` para retornar el contenido de
respuesta en un Objeto `StdClass`.


```php

use Hlmqz\Http\Requester;

$requester = new Requester('https://httpbin.org/get');

$responded = $requester->send(); // parámetro toArray por defecto false.

// ejemplo detallado con parámetro nombrado.
$responded = $requester->send(toArray: true); // si quiere que el contenido de la respuesta sea un array.

```

### Datos de respuesta de la petición

La respuesta en la variable `$responded` será la clase `Hlmqz\Http\Responded` la cuál tiene las
siguientes propiedades:

```php

// float requestDuration: Es el tiempo en segundos qué demoró la petición.
$responded->requestDuration;

// int httpCode: Es el código de respuesta HTTP de la petición,
$responded->httpCode;

// string contentType: es el tipo de contenido dado en la respuesta.
$responded->contentType;

// string url: Es la URL usada en la petición.
$responded->url;

// string method: Es el método usado en la petición.
$responded->method;

// array headers: Son las cabeceras recibidd en la petición.
$responded->headers;

// mixed content: el contenido de la respuesta, texto si no es application/json
// json u objeto, según el parámetro toArray en el método send.

$responded->content;

// mixed error: es el mensaje de error de CURL en caso de falla al realizar la petición
// sería bueno validar si tiene valor para continuar.
$responded->error;

// mixed rawData: es la data enviada en la petición, de manera como se esptableció antes de
// procesarse al tipo de contenido
$responded->rawData;

// array curlInfo: es el array con el contenido dado por curl_info, contiene
// detalles de la peticó¡ión realizada.
$responded->curlInfo;

```

## Envío multiple o masivo

Puede realizar también multiples peticiones de manera masiva, esto es útil cuando 
queire realizarlas de manera asincrónica, se enviarán al tiempo cuantas pueda realizar
el sistema sin bloquearlo, realizando de manera eficiente en tiempo.

Para realizarlo, en un instancia de `Requester` (vacía, pues ella misma no se ejecutará)
use el método `addMultiple` puede agregar de a una instancias de `Requester` que posteriormente
con el método `sendMultiple` puede realizar la ejecución de todos lo `Requester`, como respuesta
tendrá un array con todas las insancias de `Responded` correspondientes.

Este array de `Responded` está indexado con los id's de cada una de las `Requester` ingresados.

ejemplo de realizar 10 peticiones simultaneamente:

```php

use Hlmqz\Http\Requester;

$multiRquester = new Requester();

foreach (range(0, 9) as $n){
	$multiRequester->addMultiple(new Requester('https://httpbin.org/uuid'));
}

$allResponded = $multiRequester->sendMultiple(toArray: false);

foreach ($allResponded as $responded){
	print_r($responded->content);
}
```

Una implementación sencilla, fácil de usar y sin dependencias innecesarias.

