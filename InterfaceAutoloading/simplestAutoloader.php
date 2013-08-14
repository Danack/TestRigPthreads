<?php

function simplestAutoloader($className) {
    echo "Loading class: ".$className.NL;
    require $className.".php";
}


?>