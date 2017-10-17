<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "eta";
$config['db']['dbname'] = "IOB";


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

$app->get('/apiv2', function (Request $request, Response $response) {
    $response->getBody()->write("I'SmartHome API");

    return $response;
});

$app->get('/apiv2/devices', function (Request $request, Response $response) {
    $response = new Device($this->db);
    $response->find();
    return $response;
});

$app->get('/apiv2/notif/{ip}/{port}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->ip_address = $args['ip'];
    $device->port = $args['port'];
    $device->device_name = "Unknow Device";
    $device->status = "notifed";
    $device->save();
    return $response;
});

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

$app->get('/apiv2/status/{id}/{port}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $device->port = $args['port'];
    $response = $device->deviceStatus();
    
    return $response;
});

$app->get('/apiv2/delete/{id}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $response = $device->delete();
    
    return $response;
});

$app->post('/apiv2/update', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $data = $request->getParsedBody();
    $device->id = filter_var($data['id'], FILTER_SANITIZE_STRING);
    $device->device_name = filter_var($data['nama'], FILTER_SANITIZE_STRING);
    $response = $device->editName();
    return $response;
});

$app->get('/apiv2/add/{id}', function (Request $request, Response $response, $args) {
    $device = new Device($this->db);
    $device->id = $args['id'];
    $response = $device->editStatus();
    return $response;
});

$app->run();