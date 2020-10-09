<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once("model/Folder.php");
require_once("view/ClassDiagram.php");


class InterfaceView {


	public function userWantsToCreateLink()  {
		return isset($_GET["studentID"]) && isset($_GET["project"]);
	}

	public function userWantsToCheckProject()  {
		return isset($_GET["girepo"]);
	}

	public function getProject() : string {
		if (isset($_GET["project"]))
			return $_GET["project"];
		return "";
	}

	public function getStudentID() : string {
		if (isset($_GET["studentID"]))
			return $_GET["studentID"];
		return "";
	}

	public function getGitRepo() : string {
		if (isset($_GET["girepo"]))
			return $_GET["girepo"];

		else if ($this->userWantsToCreateLink()) {
			$projectID = $this->getProject();
			$studentID = $this->getStudentID();
			$gitPath = "git@gitlab.lnu.se:1dv610/student/$studentID/$projectID.git";
			return $gitPath;
		}
		return "";
	}

	public function show() {

		echo "<form action='gittest.php' method='get'>
					studentid: <input type='text' name='studentID' value=".$this->getStudentID()."></input>
		            projectid  <input type='text' name='project' value=".$this->getProject()."></input>
		       					<input type='submit'>
		            </form>
		      <form action='gittest.php' method='get'>
		      		git: <input type='text' name='girepo' value=".$this->getGitRepo()."></input>
		       					<input type='submit'>
		            </form>";
	}
}


$iv = new InterfaceView();

$iv->show();


if ($iv->userWantsToCheckProject()) {
	$gitPath = $iv->getGitRepo();

	$shellScript = "mkdir ../studentcode 2>&1
	cd ../studentcode
	git clone $gitPath 2>&1
	";
	echo "$gitPath\n" . shell_exec($shellScript);
	
	$basepath = "../studentcode";

	new \view\ClassDiagram(new \model\Folder($basepath), 
			   "");
	
	echo shell_exec("cd..
		rm -rf ../studentcode");
} else {
	echo "submit to make";
}