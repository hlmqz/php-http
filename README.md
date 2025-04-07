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
la url se pasa como primer parámetro o como parámetro nombrado como `url`,
el método puede ser pasado como segundo parámetro o como parámetro nombrado como `method`.


```php

use Hlmqz\Http\Requester;

// se puede especificando solo la URL, el método es por defecto GET
$requester = new Requester('https://jsonplaceholder.typicode.com/posts/1');

// se puede especificando solo la URL y el método
$requester = new Requester('https://jsonplaceholder.typicode.com/posts/1', 'GET');

// se puede sin especicar en la construcción, pero si asignando varaibles url y method
$requester = new Requester();
$requester->url = 'https://jsonplaceholder.typicode.com/posts/1';
$requester->method = 'GET';

```

las caracteristicas de configuración que no son obligatorias pero están disponibles son:


```php

// array methods: no se puede escribir, muestra los métodos disponibles.
$requester->methods;

// string method: establece el método a usar, por defecto 'GET'.
$requester->method = 'GET';

// array types: no se puede escribir, muestra los tipos de contenido, un array.
$requester->types;

// string type: establece el tipo de contendio a enviar, los indices de types son los válidos, por defecto 'json'.
$requester->type = 'json';

// int timeLimit: establece el tiempo límite para TIMEOUT de CURL, por defecto 30.
$requester->timeLimit = 30;

// string url: es la URL a la cual se le realizará la petición, por defecto es vacio.
$requester->url = '';

// array last: tiene los datos de la petición anterior.
$requester->last;

```

### Envío de la petición

Para realizar la petición, solo es llamar al método `send` que tiene un parámetro no obligatorio `toArray`
que definie si el contenido será devuelto como Array, por defecto es `false` para retornar el contenido de
respuesta en un Objeto `StdClass`.


```php

use Hlmqz\Http\Requester;

$requester = new Requester('https://jsonplaceholder.typicode.com/posts/1');

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

//mixed rawData: es la data enviada en la petición, de manera como se esptableció antes de
// procesarse al tipo de contenido
$responded->rawData;

//array curlInfo: es el array con el contenido dado por curl_info, contiene detalles de la peticó¡ión realizada.
$responded->curlInfo;

```

Una implementación sencilla, fácil de usar y sin dependencias innecesarias.

