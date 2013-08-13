<?php

$crash = true;


function simplestAutoloader($className) {

    echo "Loading class: ".$className."\n";

    require $className.".php";
}


spl_autoload_register('simplestAutoloader');



class TestWorker extends \Worker {

    public function __construct() {
    }

    public function run() {
        spl_autoload_register('simplestAutoloader');
    }
}


class TestTask extends \Stackable {

    var $started = false;
    var $finished = false;
    
    public function getWorker() {
        return $this->worker;
    }

    public function __construct() {
        
        $this->started = false;
        $this->finished = false;
    }

    public function run() {
        $this->started = true;
        echo "Run TestTask\n";

        echo "Creating class2 - which loads interface\n";
        $this->testClass = new TestClass2();

        $this->testClass->foo();
        
        //echo "about to sync in run\n";
        $this->synchronized(function(){
            //echo "This notify\n";
            $this->notify();
        });
        
        $this->finished = true;
    }
}


if ($crash == true) {
    echo "Creating class1 - which loads interface\n";
    $class1 = new TestClass1();
}

$worker = new TestWorker();
$worker->start();

$testTask = new TestTask();
$worker->stack($testTask);


$testTask->synchronized(function($runningTestTask){

    if (!$runningTestTask->finished == false) {
      //  echo "Doing wait in synchronized.\n";
        $runningTestTask->wait();
    }
    //echo "Done?<br/>";
}, $testTask);



?>