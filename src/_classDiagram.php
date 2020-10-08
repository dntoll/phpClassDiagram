<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once("model/Folder.php");
require_once("view/ClassDiagram.php");

// Dis is a test comment


new \view\ClassDiagram(new \model\Folder($_GET["basepath"]), 
					   $_GET["selected"]);