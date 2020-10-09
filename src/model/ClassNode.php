<?php

namespace model;


class ClassNode {
	
	public function __construct($namespace, $className, $fanout, $stringconstants) {
		$this->namespace = $namespace;
		$this->className = $className;
		$this->fanout = $fanout;
		$this->stringConstants = $stringconstants;
	}

	public function getFullName() {
		if ($this->namespace != "")
			return $this->namespace . "\\" . $this->className;
		else
			return $this->className;
	}

	public function matchStringConstants(ClassNode $other) {
		
		return array_intersect($this->stringConstants, $other->stringConstants);
	}

	public function getRelativeClassName($other, $classes) {

		if (strpos($other, "\\") === false) {
			$inThisClassNameSpace = $this->namespace . "\\" . $other;	

			foreach ($classes as $key => $class) {
				$sameNamespace = (strcmp($class->namespace, $this->namespace) == 0);
				$sameName = (strcmp($other, $this->className) == 0);
				if ($sameNamespace && $sameName) {
					echo "found $other as $inThisClassNameSpace in $class->className $this->className<br/>";
					return $inThisClassNameSpace;
				}
			}
			echo "not found $other as $inThisClassNameSpace<br/>";
			return $other;
		}
		else
			return $other;
	}
}