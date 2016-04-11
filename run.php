<?php
    require_once("vendor/autoload.php");
    require_once("Logger.php");
    require_once("elevator/ElevatorController.class.php");
    require_once("elevator/Elevator.class.php");
    require_once("vendor/phplucidframe/phplucidframe/lib/classes/console/ConsoleTable.php");


    $elevatorController = new \elevator\ElevatorController();

    $app = function ($request, $response) use (&$elevatorController) {
        Logger::getInstance()->write("=========================================");

        $inParams = $request->getQuery();

        if ( ! empty($inParams['from']) && ! empty($inParams['to'])) {

            $response->writeHead(200, array('Content-Type' => 'text/plain'));
            $from = $inParams['from'];
            $to   = $inParams['to'];
            if ($elevator = $elevatorController->elevatorRequest($from, $to)) {
                $response->write("Request from {$from} to {$to}\n");
                $response->end("Your elevator: E{$elevator->getID()}\n");
            } else {
                $response->end("All elevators is busy right now.\nPlease try again later\n");
            }
        } else {
            $response->writeHead(400, array('Content-Type' => 'text/plain'));
            $response->end();
        }
    };

    $loop = React\EventLoop\Factory::create();


    $loop->addPeriodicTimer(1, function () use (&$elevatorController) {
        echo $elevatorController->elevatorRun();
    });

    $socket = new React\Socket\Server($loop);
    $http   = new React\Http\Server($socket, $loop);

    $http->on('request', $app);

    $socket->listen(1337);
    $loop->run();