<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$config['db']['host']   = $_ENV['DB_HOST'];
$config['db']['user']   = $_ENV['DB_USER'];
$config['db']['pass']   = $_ENV['DB_PASSWORD'];
$config['db']['dbname'] = $_ENV['DB_NAME'];


$app = new \Slim\App(["settings"=>$config]);

$container = $app->getContainer();

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    };
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

// Hanya Index
$app->get('/apiv2', function (Request $request, Response $response) {
    $response->getBody()->write("I'SmartHome API");

    return $response;
});

// GET : mengambil semua device di database
// curl -X GET http://localhost/apiv2/devices/
$app->get('/apiv2/devices', function (Request $request, Response $response) {
    $response = new Device($this->db);
    $response->find();
    return $response;
});

// GET : digunakan untuk hardware mendaftar ke DB
// curl -X GET http://localhost/apiv2/notif/192.168.1.2/2
$app->get('/apiv2/notif/{ip}/{port}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->ip_address = $args['ip'];
    $device->port = $args['port'];
    $device->device_name = "Unknow Device";
    $device->status = "notifed";
    $device->save();
    return $response;
});

// GET : digunakan untuk client menghubungi API agar API menghidupkan atau mematikan device
// curl -X GET http://localhost/apiv2/device/1/1/0 #untuk off device 1
$app->get('/apiv2/device/{id}/{port}/{onf}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $device->port = $args['port'];
    if($args['onf'] == '1'){
        $response = $device->deviceOn();
    }else if($args['onf'] == '0'){
        $response = $device->deviceOff();
    }else{
        $response = "404";
    }
    return $response;
});

// GET : digunakan untuk client menghubungi API agar API memerikasa status device
// curl -X GET http://localhost/apiv2/device/1/1 #untuk memeriksa status device port 1
$app->get('/apiv2/status/{id}/{port}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $device->port = $args['port'];
    $response = $device->deviceStatus();
    
    return $response;
});

// GET : digunakan untuk client menghubungi API agar API menghapus device di database
// curl -X GET http://localhost/apiv2/delete/1
$app->get('/apiv2/delete/{id}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $response = $device->delete();
    
    return $response;
});

// POST : digunakan untuk client menghubungi API agar API mengubah nama device di database
// curl -X POST -d '{ "id":"54", "nama":"aji" }' http://localhost/apiv2/update  
$app->post('/apiv2/update', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $data = $request->getParsedBody();
    $device->id = filter_var($data['id'], FILTER_SANITIZE_STRING);
    $device->device_name = filter_var($data['nama'], FILTER_SANITIZE_STRING);
    $response = $device->editName();
    return $response;
});

// GET : digunakan untuk client menghubungi API agar API mengubah status device di database menjadi ditambahkan
// curl -X GET http://localhost/apiv2/add/1
$app->get('/apiv2/add/{id}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $response = $device->editStatus();
    return $response;
});

$app->run();