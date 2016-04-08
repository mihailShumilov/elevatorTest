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
            $elevator = $this->getNearElevator($from);
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
                    return $elevator;
                }else {
                    $elevatorDistance[$index] = $elevator->getCurrentFloor() - $floorNum;
                }
            }

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
                $table->addHeader("E{$index}");
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
                                    $row[] = "*";
                                break;
                        }
                    }else{
                        $row[] = " ";
                    }
                }
                $table->addRow($row);
            }
            $table->display();
        }
    }