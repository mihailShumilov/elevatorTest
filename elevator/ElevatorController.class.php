<?php
    /**
     * Created by PhpStorm.
     * User: godson
     * Date: 4/8/16
     * Time: 15:37
     */

    namespace elevator;


    use LucidFrame\Console\ConsoleTable;

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

        public function __construct($maxFloorCount = 10, $maxElevatorCount = 4)
        {
            $this->maxFloorCount    = $maxFloorCount;
            $this->maxElevatorCount = $maxElevatorCount;
            $this->initElevators();
        }

        private function initElevators()
        {
            $floorList = range(1, $this->maxFloorCount);

            for ($i = 1; $i <= $this->maxElevatorCount; $i++) {
                $this->elevatorList[] = new Elevator($floorList, intval(rand(1, $this->maxFloorCount)), $i);
            }
        }

        /**
         * @param $from
         * @param $to
         */
        public function elevatorRequest($from, $to){
            \Logger::getInstance()->write("Income request from {$from} to {$to} floor");
            $elevator = $this->getNearElevator($from);
            \Logger::getInstance()->write("Near elevator: {$elevator->getID()} that located at {$elevator->getCurrentFloor()} floor");
            if($elevator->getCurrentFloor() != $from){
                $elevator->moveTo($from);
            }
            $elevator->moveTo($to);
        }


        /**
         * @param $floorNum
         * @return Elevator
         */
        private function getNearElevator($floorNum){
            $elevatorDistance = [];
            foreach($this->elevatorList as $index=>$elevator){
                if($elevator->getCurrentFloor() == $floorNum){
                    if(Elevator::DIRECTION_STAND == $elevator->getCurrentState()) {
                        return $elevator;
                    }
                }else {
                    if(Elevator::DIRECTION_STAND == $elevator->getCurrentState()) {
                        $distance = $elevator->getCurrentFloor() - $floorNum;
                        if ($distance < 0) {
                            $distance *= -1;
                        }
                        $elevatorDistance[$index] = $distance;
                    }
                }
            }
            $item = array_keys($elevatorDistance, min($elevatorDistance));
            return $this->elevatorList[$item[0]];
        }

        public function elevatorRun(){
            foreach($this->elevatorList as $elevator){
                $elevator->move();
            }
            $this->drawCurrentState();
        }

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
                    }elseif($elevator->getDestinationFloor() == $floor){
                        $row[] = "x";
                    }else{
                        $row[] = " ";
                    }
                }
                $table->addRow($row);
            }
            $table->display();
            echo date("Y-m-d H:i:s").PHP_EOL;
            echo "Server running at http://127.0.0.1:1337".PHP_EOL;
        }
    }