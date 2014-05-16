<?php

require_once("model/Folder.php");
require_once("view/ClassDiagram.php");

// Dis is a test comment


new \view\ClassDiagram(new \model\Folder($_GET["basepath"]), 
					   $_GET["selected"]);