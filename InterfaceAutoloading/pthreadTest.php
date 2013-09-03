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
        echo "\nRun TestTask\n";

        echo "\nAbout to crash?\n";
        $this->testClass = new TestClass2();

        $this->testClass->foo();
        
        $this->finished = true;
        
        //echo "about to sync in run\n";
        $this->synchronized(function(){    
            //echo "This notify\n";
            $this->notify();
        });
        
        
    }
}

$class1 = false;
if ($crash == true) {
    echo "Creating class1 - which loads interface\n";
    $class1 = new TestClass1();
}

$worker = new TestWorker();
$worker->start();

$testTask = new TestTask();

echo "\nworker->stack\n";
$worker->stack($testTask);


$testTask->synchronized(function($runningTestTask){

    if (!$runningTestTask->finished) {
      //  echo "Doing wait in synchronized.\n";
        $runningTestTask->wait();
    }
    //echo "Done?<br/>";
}, $testTask);


echo "Did not crash!";

var_dump($class1);

?>
