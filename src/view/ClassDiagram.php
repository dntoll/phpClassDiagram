<?php

namespace view;

require_once("model/Folder.php");
require_once("model/ProjectParser.php");

class ClassDiagram {
	public function __construct(\model\Folder $source, $className) {
		

		$parser = new \model\ProjectParser($source);
		
		$classes = $parser->getClasses();

		

		$includedClasses = $this->getIncludedClasses($classes, $className);
		$relations = $this->getRelations($classes, $includedClasses);

		$hiddenrelations = $this->findHiddenDependencies($classes);

		$includedNamespaces= $this->getIncludedNamespaces($includedClasses);
		$namespaceRelations= $this->getIncludedNamespaceRelations($relations, $hiddenrelations);
		echo $this->getImageLink($includedNamespaces, $namespaceRelations, $hiddenrelations);
		echo $this->getRaw($includedNamespaces, $namespaceRelations, $hiddenrelations);

		echo $this->getImageLink($includedClasses, $relations, $hiddenrelations);
		echo $this->getRaw($includedClasses, $relations, $hiddenrelations);
	}


	private function findHiddenDependencies(array $classes) : array {
		$ret = array();
		foreach ($classes as $outer => $first) {
			foreach ($classes as $inner => $second) {
				if ($inner > $outer) {
					$matches = $first->matchStringConstants($second);
					if (count($matches) > 0) {
						$ret[] = array($first, $second, $matches);
						var_dump($matches);
					}
				}

			}
		}

		return $ret;
	}

	private function getImageLink($includedClasses, $relations, $hiddenrelations) {
		$string = "http://yuml.me/diagram/plain;dir:LR;scale:80;/class/";

		$first = true;

		foreach($relations as $relation) {
			//var_dump($relation);
			$fromFN = $relation[0];
			$toFN = $relation[1];

			$from = $this->yumlClassName($fromFN, "");
			$to = $this->yumlClassName($toFN , "");

			if ($first) {
				$first = false;
			} else {
				$string .= ",";
			}
			if ($relation[2])
				$string .= urlencode("[$from]->[$to]");
			else
				$string .= urlencode("[$from]-.-[$to]");
		}
		return "<img src='$string'/>";
	}

	private function getRaw($includedClasses, $relations, $hiddenrelations) {
		$first = true;

		$string= "";
		foreach($relations as $relation) {
			//var_dump($relation);
			$from = $relation[0];
			$to = $relation[1];

			if ($first) {
				$first = false;
			} else {
				$string .= ",";
			}
			$string .= ("[$from]->[$to] \<br/>");
		}
		foreach($hiddenrelations as $relation) {
			//var_dump($relation);
			$from = $relation[0]->getFullName();
			$to = $relation[1]->getFullName();

			$what = array_shift($relation[2]);

			
			if ($first) {
				$first = false;
			} else {
				$string .= ",";
			}
			$string .= ("[$from]..[$to] \"$what\"\<br/>");
		}

		return $string;
	}

	private function getRelations($classes, $includedClasses) {
		$ret = array();

		
		foreach($classes as $class) {

			$fullName = "$class->namespace\\$class->className";

			$isIncluded = isset($includedClasses[$fullName]);
			$isIncluded |=isset($includedClasses[$class->className]);

			$isIncluded = true;


			if ($isIncluded) {



				foreach($class->fanout as $other) {
					$otherClass= $this->findClass($classes, $other, $class->namespace);

					
					if(isset($includedClasses[$otherClass->getFullName()])) {

						$ret[] = array($class->getFullName(), $otherClass->getFullName(), true);
					}
				}
			}
			
		}
		return $ret;
	}

	private function getIncludedNamespaceRelations($relations, $hiddenrelations):array {

		$ret = array();
		foreach ($relations as $key => $pair) {
			$from = $this->getMergedNamespace($pair[0]);
			$to = $this->getMergedNamespace($pair[1]);

			$ret[$from.$to] = array($from , $to, true );
		}

		foreach ($hiddenrelations as $key => $pair) {
			$from = $this->getMergedNamespace($pair[0]->getFullName());
			$to = $this->getMergedNamespace($pair[1]->getFullName());

			$ret[$from.$to] = array($from , $to, false );
		}

		return $ret;
	}


	private function getIncludedClasses($classes, $className) {
		$includedClasses = array($className => $className);

		
		foreach($classes as $class) {
			$left = strcmp($class->getFullName(), $className)==0;
			

			foreach($class->fanout as $other) {
				$otherClass = $this->findClass($classes, $other, $class->namespace);

				
				$right = strcmp($otherClass->getFullName(), $className) == 0;
				
				if ($left || $right || true) {
					$includedClasses[$otherClass->getFullName()] = $otherClass->getFullName();
					$includedClasses[$class->getFullName()] = $class->getFullName();
				}
			}
		}
		return $includedClasses;
	}


	private function getIncludedNamespaces($classes) {
		$ret = array();
		foreach ($classes as $key => $className) {
			$namespaceName  = $this->getNamespace($className);;
			$ret[$namespaceName] = $namespaceName;
			
		}

		return $ret;
	}

	private function getNamespace(string $className) : string {
			$posOfLast = strrpos($className, "\\");
			if ($posOfLast === FALSE) {
				return "";
			} else {
				return substr($className, 0, $posOfLast);
			}
	}

	private function getMergedNamespace(string $className) : string {
			$posOfLast = strrpos($className, "\\");
			if ($posOfLast === FALSE) {
				return $className;
			} else {
				return substr($className, 0, $posOfLast) . "\\Merged";
			}
	}
	
	private function findClass($classes, $class, $localNamespace)  {
		$lastPos = strpos($class, "\\");
		if ($lastPos !== FALSE) {
			return new \model\ClassNode("", $class, array(), array());
		}
		
		//find in same namespace
		for ($i = 0; $i < count($classes); $i++) {
			$maybe = $classes[$i];
			
			if ($localNamespace == $maybe->namespace) {
				if ($class == $maybe->className) {
					return $maybe;
				}
			}
		}
		
		return new \model\ClassNode("", $class, array(), array());
	}
	
	private function yumlClassName($className, $namespace) {
		
		$color = $this->getColor($className, $namespace);
		
		if (strpos($className, "\\") === FALSE && strlen($namespace) > 0 ) {
			$className = $namespace . "\\" . $className;
		}
		
		$name = str_replace("\\", "::", $className);
		
		
		
		return $name . $color;
	}
	
	private $namespacesFound = array();
	
	private function getColor($className, $namespace) {

		if (strpos($className, "\\") !== FALSE) {
			$last = strrpos($className, "\\");
			$namespace = substr($className, 0, $last);
		}
		
		$colors = array("green", "orange", "red", "blue", "gray", "lightblue", "pink", "lightgreen");
		
		
		for ($i = 0; $i < count($this->namespacesFound); $i++) {
			if ($this->namespacesFound[$i] == $namespace) {
				$color = $colors[$i % (count($colors))];
				return "{bg:$color}";
			}
		} 
		if ($namespace != "") {
			$this->namespacesFound[] = $namespace;
			
			$i = count($this->namespacesFound)-1;
			$color = $colors[$i % (count($colors))];
			
			
			return "{bg:$color}";
		} else {
			return "";
		}
		
	}
	
	public static function ajaxIncludeImage($className, \model\Folder $sourceFolder) {
		$basepath = $sourceFolder->getFullName();
		return "<div id='minDivTag'>ClassDiagram</div><script>
function callback(serverData, serverStatus, id) {
        if(serverStatus == 200){
                document.getElementById(id).innerHTML = serverData;
        } else {
                document.getElementById(id).innerHTML = 'Loading diagram...'; 
        }
}
 
function ajaxRequest(openThis, id) {
 
   var AJAX = null; 
   if (window.XMLHttpRequest) { 
      AJAX=new XMLHttpRequest(); 
   } else {
      AJAX=new ActiveXObject('Microsoft.XMLHTTP'); 
   }
   if (AJAX == null) { 
      return false; 
   }
   AJAX.onreadystatechange = function() { 
      if (AJAX.readyState == 4 || AJAX.readyState == 'complete') { 
         callback(AJAX.responseText, AJAX.status, id);
      }  else { 
		  document.getElementById(id).innerHTML = 'Loading...'; 
      } 
   }
   
   var url= openThis; 
   AJAX.open('GET', url, true); 
   AJAX.send(null); 
}
 
ajaxRequest('_classDiagram.php?basepath=$basepath&selected=$className', 'minDivTag');
</script>

 
";
	}
}



