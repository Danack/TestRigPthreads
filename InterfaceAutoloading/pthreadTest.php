<?php



$setupCrash = true;
$mainThreadStillRunning = true;


require "bootstrap.php";
require "simplestAutoloader.php";


//$shareMode = PTHREADS_INHERIT_NONE;
//$shareMode = PTHREADS_INHERIT_FUNCTIONS
$shareMode = PTHREADS_INHERIT_ALL;

spl_autoload_register('simplestAutoloader');


class TestWorker extends \Worker {

    var $shareMode;
    
    public function __construct($shareMode) {
        $this->shareMode = $shareMode;
    }

    public function run() {
        
        echo "Start worker run\n";

        if (($this->shareMode & PTHREADS_INHERIT_INCLUDES) == 0){
            if (($this->shareMode & PTHREADS_INHERIT_CONSTANTS) == 0){
                require "bootstrap.php";
            }
        }

        if (($this->shareMode & PTHREADS_INHERIT_FUNCTIONS) == 0){ 
            require "simplestAutoloader.php";
        }
        spl_autoload_register('simplestAutoloader');
        
        echo "Running: \n";
    }
}


class TestTask extends \Stackable {

    public function getWorker() {
        return $this->worker;
    }

    public function __construct() {
    }

    public function run() {

        echo "Run TestTask".NL;

        sleep(1);
        
        echo "Creating class2 - which loads interface\n";
        $this->testClass = new TestClass2();

    }
}


if ($setupCrash == true) {
    echo "Creating class1 - which loads interface\n";
    $class1 = new TestClass1();
}

$worker = new TestWorker($shareMode);
$worker->start($shareMode);

$testTask = new TestTask();
$worker->stack($testTask);

if ($mainThreadStillRunning) {
    sleep(2);
}



?>
