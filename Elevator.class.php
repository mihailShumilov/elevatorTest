<?php
    /**
     * Created by PhpStorm.
     * User: godson
     * Date: 4/8/16
     * Time: 15:16
     */

    namespace elevator;


    class Elevator
    {
        const DIRECTION_UP          = 'up';
        const DIRECTION_DOWN        = 'down';
        const DIRECTION_STAND       = 'stand';
        const DIRECTION_MAINTENANCE = 'maintenance';

        const EVENT_ALARM      = 'alarm';
        const EVENT_DOOR_OPEN  = 'door open';
        const EVENT_DOOR_CLOSE = 'door close';


        private $currentFloor = 1;

        private $destinationFloor = 1;

        private $currentState = self::DIRECTION_STAND;

        private $lastEvent = false;

        private $floorList = [];

        private $id = 0;


        public function __construct(array $floorList, $currentFloor = 1, $id = 0)
        {
            $this->floorList    = $floorList;
            $this->currentFloor = $currentFloor;
            $this->id           = $id;
        }

        public function moveTo($floorNum)
        {
            if ($floorNum) {
                if(in_array($floorNum, $this->floorList)) {
                    $this->destinationFloor = $floorNum;
                }else{
                    $this->raiseEvent('bad floor num');
                }
            } else {
                $this->raiseEvent('floor num not set');
            }
        }

        public function move(){
            if($this->destinationFloor != $this->currentFloor){
                if($this->destinationFloor < $this->currentFloor){
                    $this->currentState = self::DIRECTION_DOWN;
                    $this->currentFloor--;
                }else{
                    $this->currentState = self::DIRECTION_UP;
                    $this->currentFloor++;
                }
            }else{
                if(in_array($this->currentState, [self::DIRECTION_DOWN, self::DIRECTION_UP] )){
                    $this->currentState = self::DIRECTION_STAND;
                    $this->raiseEvent(self::EVENT_DOOR_OPEN);
                }else{
                    if(self::EVENT_DOOR_OPEN == $this->lastEvent){
                        $this->raiseEvent(self::EVENT_DOOR_CLOSE);
                    }
                }
            }
        }

        protected function raiseEvent($eventName)
        {
            $this->lastEvent = $eventName;
            switch ($eventName) {
                case self::EVENT_ALARM:
                    echo "Elevator {$this->id} send alarm event";
                    break;
                case self::EVENT_DOOR_OPEN:
                    echo "Elevator {$this->id} send door open event";
                    break;
                case self::EVENT_DOOR_CLOSE:
                    echo "Elevator {$this->id} send door close event";
                    break;
                default:
                    echo "Elevator {$this->id} send unknown event";
                    break;
            }
        }

        public function getCurrentFloor(){
            return $this->currentFloor;
        }
    }