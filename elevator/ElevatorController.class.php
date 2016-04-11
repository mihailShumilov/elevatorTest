<?php
    namespace elevator;


    use LucidFrame\Console\ConsoleTable;

    /**
     * Class ElevatorController
     *
     * @package elevator
     */
    class ElevatorController
    {
        /**
         * @var Elevator[]
         */
        private $elevatorList = [];

        /**
         * @var int
         */
        private $maxFloorCount = 10;

        /**
         * @var int
         */
        private $maxElevatorCount = 4;

        /**
         * ElevatorController constructor.
         *
         * @param int $maxFloorCount
         * @param int $maxElevatorCount
         */
        public function __construct($maxFloorCount = 10, $maxElevatorCount = 4)
        {
            $this->maxFloorCount    = $maxFloorCount;
            $this->maxElevatorCount = $maxElevatorCount;
            $this->initElevators();
        }

        /**
         * Init elevators
         */
        private function initElevators()
        {
            $floorList = range(1, $this->maxFloorCount);

            for ($i = 1; $i <= $this->maxElevatorCount; $i++) {
                $this->elevatorList[] = new Elevator($floorList, intval(rand(1, $this->maxFloorCount)), $i);
            }
        }

        /**
         * Process elevator request
         *
         * @param $from
         * @param $to
         *
         * @return bool|Elevator
         */
        public function elevatorRequest($from, $to){
            \Logger::getInstance()->write("Income request from {$from} to {$to} floor");
            if ($elevator = $this->getNearElevator($from)) {
                \Logger::getInstance()->write("Near elevator: {$elevator->getID()} that located at {$elevator->getCurrentFloor()} floor");
                if ($elevator->getCurrentFloor() != $from) {
                    $elevator->moveTo($from);
                }
                $elevator->moveTo($to);
                return $elevator;
            } else {
                \Logger::getInstance()->write("All Elevators is busy right now");
                return false;
            }
        }


        /**
         * Return near elevator or false if all elevators is busy
         *
         * @param $floorNum
         *
         * @return bool|Elevator
         */
        private function getNearElevator($floorNum){
            $elevatorDistance = [];
            foreach($this->elevatorList as $index=>$elevator){
                if($elevator->getCurrentFloor() == $floorNum){
                    if (Elevator::MAIN_STATE_IDLE == $elevator->getMainState()) {
                        return $elevator;
                    }
                }else {
                    if (Elevator::MAIN_STATE_IDLE == $elevator->getMainState()) {
                        $distance = $elevator->getCurrentFloor() - $floorNum;
                        if ($distance < 0) {
                            $distance *= -1;
                        }
                        $elevatorDistance[$index] = $distance;
                    }
                }
            }

            if ( ! empty($elevatorDistance)) {
                $item = array_keys($elevatorDistance, min($elevatorDistance));
                return isset($this->elevatorList[$item[0]]) ? $this->elevatorList[$item[0]] : false;
            } else {
                return false;
            }
        }

        /**
         * Run elevator moving
         */
        public function elevatorRun(){
            foreach($this->elevatorList as $elevator){
                $elevator->move();
            }
            $this->drawCurrentState();
        }

        /**
         * Display current system state
         */
        private function drawCurrentState(){
            system('clear');
            $table = new ConsoleTable();
            $table->addHeader("Floor");
            foreach($this->elevatorList as $index =>$elevator){
                if($elevator->getCurrentState() != Elevator::DIRECTION_STAND) {
                    $table->addHeader("-E{$elevator->getID()}-");
                }else{
                    $table->addHeader(" E{$elevator->getID()} ");
                }
            }
            for($floor = $this->maxFloorCount; $floor >= 1; $floor--){
                $row = [];
                $row[] = $floor;
                foreach($this->elevatorList as $elevator){
                    if($elevator->getCurrentFloor() == $floor){
                        switch($elevator->getCurrentState()){
                            case Elevator::DIRECTION_UP:
                                    $row[] = "<";
                                break;
                            case Elevator::DIRECTION_DOWN;
                                    $row[] = ">";
                                break;
                            default:
                                switch($elevator->getLastEvent()){
                                    case Elevator::EVENT_DOOR_OPEN:
                                        $row[] = "=";
                                        break;
                                    default:
                                            $row[] = "*";
                                        break;
                                }

                                break;
                        }
                    } else if ($elevator->isDestination($floor)) {
                        $row[] = "x";
                    } else if ($elevator->getDestinationFloor() == $floor) {
                        $row[] = "x1";
                    }else{
                        $row[] = " ";
                    }
                }
                $table->addRow($row);
            }
            $table->display();
            echo date("Y-m-d H:i:s").PHP_EOL;
            foreach ($this->elevatorList as $elevator) {
                echo "Elevator {$elevator->getID()} state: {$elevator->getCurrentState()} at {$elevator->getCurrentFloor()} floor with main state {$elevator->getMainState()}" . PHP_EOL;
            }
            $this->drawAgenda();
        }

        /**
         * Display agenda
         */
        private function drawAgenda()
        {
            echo "----------------------------------------" . PHP_EOL;
            echo "Agenda" . PHP_EOL;
            echo "\t*\t-\televator in idle state" . PHP_EOL;
            echo "\t>\t-\televator move down" . PHP_EOL;
            echo "\t<\t-\televator move up" . PHP_EOL;
            echo "\t=\t-\televator open door" . PHP_EOL;
            echo "\tx\t-\tone of stop elevator point" . PHP_EOL;
            echo "\tx1\t-\tcurrent elevator destination" . PHP_EOL;
            echo "Server running at http://127.0.0.1:1337" . PHP_EOL;
        }
    }