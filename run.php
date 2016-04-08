<?php
    /**
     * Created by PhpStorm.
     * User: godson
     * Date: 4/8/16
     * Time: 16:45
     */



    require_once("vendor/autoload.php");
    require_once("elevator/ElevatorController.class.php");
    require_once("elevator/Elevator.class.php");
    require_once ("vendor/phplucidframe/phplucidframe/lib/classes/console/ConsoleTable.php");


    $elevatorController = new \elevator\ElevatorController();

    $app = function ($request, $response) use (&$elevatorController) {
        $response->writeHead(200, array('Content-Type' => 'text/plain'));
        $from = intval(rand(1, 10));
        $to = intval(rand(1, 10));
        $elevatorController->elevatorRequest($from,$to);
        $response->end("Request from {$from} to {$to}\n");
    };

    $loop = React\EventLoop\Factory::create();



    $loop->addPeriodicTimer(1, function () use (&$elevatorController) {
        echo $elevatorController->elevatorRun();
    });

    $socket = new React\Socket\Server($loop);
    $http   = new React\Http\Server($socket, $loop);

    $http->on('request', $app);
    echo "Server running at http://127.0.0.1:1337\n";

    $socket->listen(1337);
    $loop->run();