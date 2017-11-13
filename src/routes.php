<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->add(new \Slim\Middleware\JwtAuthentication([
    "attribute" => "jwt",
    "secret" => "secretAbc",
    "callback" => function ($request, $response,$arguments) use ($container) {
        $container['jwt'] = $arguments['decoded'];       
    },
    "error" => function ($request, $response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response->write("Unauthorized");
            /* ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); */
    },
    "rules" => [
        new \Slim\Middleware\JwtAuthentication\RequestPathRule([
            "path" => "/apiv2",
            "passthrough" => ["/apiv2/auth","/apiv2/index"]
        ])
    ]
]));

// Hanya Index
$app->get('/apiv2/index', function (Request $request, Response $response) {
    $response->getBody()->write('Mulai Akses ke I\' SmartHome ...');
    return $response;
});


// Authentification
$app->get('/apiv2/auth',function (Request $request,Response $response){
    $users = new User($this->db);
    
    $response->getBody()->write($user->login());
    return $users->login();
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