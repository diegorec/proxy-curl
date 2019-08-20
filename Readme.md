
> Biblioteca de consultas cURL con posibilidad de cacheo

# Inicio rápido y ejemplos
La biblioteca PROXY nos permite realizar conexiones HTTP mediante el uso de la biblioteca [\CURL\CURL](https://github.com/php-curl-class/php-curl-class)

## PROXY con cacheo en ficheros
Configuramos un PROXY para consultar una dirección, cacheando el resultado en un documento JSON y devolverá el resultado. La siguiente vez que hagamos la consulta, si el tiempo indicado no está superado, el PROXY devolverá el contenido directamente de la caché.

```php
use proxy\Proxy;
use proxy\cache\FileCache;

$url = "https://httpbin.org/get";

$filecache = new FileCache();
$filecache->onInit([
    "dirname" => __DIR__ . "/temp/"
]);
$proxy = new Proxy($filecache);
$respuesta = $proxy->remember($url, "fichero.json", "+10 minutes");
echo json_encode($respuesta);
```

Veamos una comparativa de tiempos:
1. Consulta no cachada
```bash
real	0m0,530s
user	0m0,038s
sys	0m0,013s
```

2. Consulta anterior después de ser cacheada
```bash
real	0m0,031s
user	0m0,023s
sys	0m0,008s
```

Vemos que la reducción es de un 94,15% del tiempo necesario para obtener la respuesta.

## PROXY con cacheo base de datos SQL
Para esta funcionalidad se usa la biblioteca [\Medoo\Medoo](https://medoo.in/)

```php
use proxy\Proxy;
use proxy\cache\DatabaseCache;

$url = "https://httpbin.org/get";

$databasecache = new DatabaseCache();
$databasecache->onInit([
    "database" => [
        'database_type' => 'mysql',
        'database_name' => 'cache',
        'server' => '192.168.1.8',
        'username' => 'diego',
        'password' => '.#diego#.',
    ],
    "key-column" => "c",
    "value-column" => "v",
    "time-column" => "e",
    "tags-column" => "t",
]);
$proxy = new Proxy($databasecache);
$respuesta = $proxy->remember($url, "201908191255", "+10 minutes");
echo json_encode($respuesta);
```

## Otros métodos PROXY
Si no se quiere utilizar la caché, debemos indicar el controlador ```\proxy\cache\NoCache```.

En cualquier caso, utilizando cualquier otro controlador, podremos llamar a la funcionalidad ```\proxy\Proxy::call```, la cual realizará una llamada cURL que no será cacheada ni recuperada de caché.

# Controladores 
## Interfaz CacheInterface
Los controladores de caché implementan la interfaz ```\proxy\cache\CacheInterface```, de forma que todos los controladores pueden ser utilizados por la biblioteca ```\proxy\Proxy``` indistintamente.

Los métodos que implementa son (cuya explicación se puede obtener en el PHPDoc):
```php
<?php
proxy\cache\CacheInterface::onInit(Array $params = []);  
proxy\cache\CacheInterface::get(string $name);
proxy\cache\CacheInterface::put(string $name, $content);
proxy\cache\CacheInterface::drop(string $name);
proxy\cache\CacheInterface::exists(string $name): bool;
proxy\cache\CacheInterface::hasExpired(string $name, string $expires): bool;
proxy\cache\CacheInterface::pull(string $name);
proxy\cache\CacheInterface::list(): Array;
proxy\cache\CacheInterface::flush();
proxy\cache\CacheInterface::purge(string $expires);
proxy\cache\CacheInterface::count(): int;
```

### onInit
Cuando iniciamos un controlador el primer método que tenemos que llamar es ```onInit``` dado que éste nos sirve para inicializar las variables. 
Se le pasará un array con los parámetros necesarios. En caso de que no se indique un parámetro necesario, se lanzará una excepción.


## Controlador DatabaseCache
### Parámetros onInit
Se deberán pasar las siguientes variables:
```php
$array = [
    "database" => [], // array con la estructura de configuración de un objeto Medoo para gestión de SQL
    "key-column" => "clave", // Columna de la tabla que contendrá el string con el nombre del recurso
    "value-column" => "valor", // Columna de la tabla que contendrá el propio recurso
    "time-column" => "expira",  // Columna de la tabla que contendrá la fecha de creación de un recurso
    "tags-column" => "tags" //  Columna de la tabla que contendrá las etiquetas del recurso
];
```
A mayores de las funcionalidades propias de la interfaz, este controlador nos permite que utilicemos etiquetas de contenido:

```php
proxy\cache\DatabaseCache::addTags(string $name, Array $tags) ;
proxy\cache\DatabaseCache::selectByTags(Array $tags);
```
## Controlador FileCache
### Parámetros onInit
Se deberán pasar las siguientes variables:
```php
$array = [
    "dirname" => __DIR__ . "/temp/" // Ruta a la carpeta que contendrá los archivos con la caché
];
```

## Algunas notas
Quizás haya puntos que en el futuro sean difíciles de comprender, como por ejemplo:
1. ¿Por qué se calcula en cada consulta si un elemento ha expirado o no?

Porque el controlador de caché ```\proxy\cache\FileCache``` no nos permite marcar una fecha de expiración del contenido de forma trivial, como en el caso de ```\proxy\cache\DatabaseCache```. En este caso, tenemos que calcular cual es la fecha de creación y, sobre una marca temporal data, determinar si su contenido nos vale o no.

Al utilizar la Interfaz ```\proxy\cache\CacheInterface```, todos los controladores que la extiendan tienen que tener una E/S igual, lo que nos fuerza a llevar esta limitación a los demás controladores.

Sin embargo, esto no debe verse como una limitación per se, ya que nos permite, sobre un caché ya almacenado, modificar su periodo de validez sobre la marcha, de forma que podemos ir analizando en cada consulta si debemos modificar este periodo de validez con una flexibilidad de la que no dispondríamos en el caso opuesto.