<?php
    namespace elevator;

    /**
     * Class Elevator
     *
     * @package elevator
     */
    class Elevator
    {
        const DIRECTION_UP          = 'up';
        const DIRECTION_DOWN        = 'down';
        const DIRECTION_STAND       = 'stand';
        const DIRECTION_MAINTENANCE = 'maintenance';

        const EVENT_ALARM      = 'alarm';
        const EVENT_DOOR_OPEN  = 'door open';
        const EVENT_DOOR_CLOSE = 'door close';

        const MAIN_STATE_IDLE = 'idle';
        const MAIN_STATE_WORK = 'work';


        private $currentFloor = 1;

        private $destinationFloor = false;

        private $currentState = self::DIRECTION_STAND;

        private $lastEvent = self::EVENT_DOOR_CLOSE;

        private $floorList = [];

        private $id = 0;

        private $destinationPool = [];

        private $mainState;

        /**
         * Elevator constructor.
         *
         * @param array $floorList
         * @param int $currentFloor
         * @param int $id
         */
        public function __construct(array $floorList, $currentFloor = 1, $id = 0)
        {
            $this->floorList    = $floorList;
            $this->currentFloor = $currentFloor;
            $this->id           = $id;
            $this->mainState    = self::MAIN_STATE_IDLE;
        }

        /**
         * Request to elevator moving
         *
         * @param $floorNum
         */
        public function moveTo($floorNum)
        {
            \Logger::getInstance()->write("Elevator {$this->id} got request go to {$floorNum}");
            if ($floorNum) {
                if(in_array($floorNum, $this->floorList)) {
                    $this->destinationPool[] = $floorNum;
                }else{
                    $this->raiseEvent('bad floor num');
                }
            } else {
                $this->raiseEvent('floor num not set');
            }
        }

        /**
         * Main moving function
         */
        public function move(){
            if(!empty($this->destinationPool)&&(!$this->destinationFloor)){
                $this->destinationFloor = array_shift($this->destinationPool);
            }
            if($this->destinationFloor) {
                $this->mainState = self::MAIN_STATE_WORK;
                if ($this->destinationFloor != $this->currentFloor) {
                    if ($this->destinationFloor < $this->currentFloor) {
                        $this->currentState = self::DIRECTION_DOWN;
                        $this->currentFloor--;
                    } else {
                        $this->currentState = self::DIRECTION_UP;
                        $this->currentFloor++;
                    }
                } else {
                    if (in_array($this->currentState, [self::DIRECTION_DOWN, self::DIRECTION_UP])) {
                        $this->currentState = self::DIRECTION_STAND;
                        $this->raiseEvent(self::EVENT_DOOR_OPEN);
                    } else {
                        if (self::EVENT_DOOR_OPEN == $this->lastEvent) {
                            $this->raiseEvent(self::EVENT_DOOR_CLOSE);
                            $this->destinationFloor = false;
                            if (empty($this->destinationPool)) {
                                $this->mainState = self::MAIN_STATE_IDLE;
                            }
                        } else {
                            $this->raiseEvent(self::EVENT_DOOR_OPEN);
                        }

                    }
                }
            }
        }

        /**
         * Raise elevator event
         *
         * @param $eventName
         */
        protected function raiseEvent($eventName)
        {
            $this->lastEvent = $eventName;
            switch ($eventName) {
                case self::EVENT_ALARM:
                    $logStr = "Elevator {$this->id} send alarm event";
                    break;
                case self::EVENT_DOOR_OPEN:
                    $logStr = "Elevator {$this->id} send door open event";
                    break;
                case self::EVENT_DOOR_CLOSE:
                    $logStr = "Elevator {$this->id} send door close event";
                    break;
                default:
                    $logStr = "Elevator {$this->id} send unknown event";
                    break;
            }

            \Logger::getInstance()->write($logStr);
        }

        /**
         * @return int
         */
        public function getCurrentFloor(){
            return $this->currentFloor;
        }

        /**
         * @return string
         */
        public function getCurrentState(){
            return $this->currentState;
        }

        /**
         * @return bool|int
         */
        public function getDestinationFloor(){
            return $this->destinationFloor;
        }

        /**
         * @return string
         */
        public function getLastEvent(){
            return $this->lastEvent;
        }

        /**
         * @return int
         */
        public function getID(){
            return $this->id;
        }

        /**
         * @param $floor
         *
         * @return bool
         */
        public function isDestination($floor)
        {
            if ($floor) {
                return in_array($floor, $this->destinationPool);
            }
            return false;
        }

        /**
         * @return string
         */
        public function getMainState()
        {
            return $this->mainState;
        }
    }