<?php
/**
 * @package Web
 */

global $OmniscientBeing;

// DEPRECATED! Use Application::SetStartUpPage instead.
function SetStartUpPage($className, $unsupportedURL='', $urlTokenMode=URL::Display, $tokenTrailsExpiration=604800)
{
	new Application($className, $unsupportedURL, $urlTokenMode, $tokenTrailsExpiration);
}

/**
 * @ignore
 */
/*function NOLOHErrorHandler($errno, $errstr)
{
	//print("var err=document.createElement('DIV'); err.innerHTML='$errstr'; err.style.zdocument.body.appendChild(err);");
	//if($errnor == 1 || $errno == 4 || $errno == 16 || $errno == 64 || $errno == 256)
	//{
		print("alert('Errorno $errno : $errstr');");
		//die();
	//}
}*/

/**
* @ignore
*/
final class Application
{
	private $WebPage;
	
	public static function SetStartUpPage($className, $unsupportedURL='', $urlTokenMode=URL::Display, $tokenTrailsExpiration=604800)
	{
		new Application($className, $unsupportedURL, $urlTokenMode, $tokenTrailsExpiration);
	}
	
	/**
	 * Resets Application to original state
	 * @param boolean $clearURLTokens Whether the URL Tokens will be cleared out
	 * @param boolean $clearSessionVariables Whether the session will be cleared out
	 */
	public static function Reset($clearURLTokens = true, $clearSessionVariables = true)
	{
		if($clearSessionVariables)
		{
			session_destroy();
			session_unset();
		}
		else
			self::UnsetNolohSessionVars();
		$url = $clearURLTokens ? ('"'.$_SERVER['PHP_SELF'].'"') : 'location.href';
		if(GetBrowser()=='ie')
			print('/*~NScript~*/location.replace('.$url.');');
		else
			print('/*~NScript~*/var frm = document.createElement("FORM"); frm.action = '.$url.'; frm.method = "post"; document.body.appendChild(frm); frm.submit();');
		die();
	}
	
	public function Application($className, $unsupportedURL, $urlTokenMode, $tokenTrailsExpiration)
	{
		session_name(hash('md5', $_SERVER['PHP_SELF']));
		session_start();
		$GLOBALS['NOLOHURLTokenMode'] = $urlTokenMode;
		$GLOBALS['NOLOHTokenTrailsExpiration'] = $tokenTrailsExpiration;
		if(isset($_GET['NOLOHImage']))
			Image::MagicGeneration($_GET['NOLOHImage'], $_GET['Class'], $_GET['Function']);
		elseif(isset($_GET['NOLOHFileUpload']))
			FileUpload::ShowInside($_GET['NOLOHFileUpload'], $_GET['Width'], $_GET['Height']);
		elseif(isset($_GET['NOLOHFileRequest']))
			File::SendRequestedFile($_GET['NOLOHFileRequest']);
		elseif(isset($_SESSION['NOLOHVisit']) || isset($_POST['NOLOHVisit']))
		{
			if(!isset($_SESSION['NOLOHVisit']) || (isset($_POST['NOLOHVisit']) && $_SESSION['NOLOHVisit'] != $_POST['NOLOHVisit']) ||
			  ((!isset($_POST['NOLOHVisit']) || /*!isset($_POST['NOLOHServerEvent']) || */!isset($_SERVER['HTTP_REMOTE_SCRIPTING'])) && $_SESSION['NOLOHVisit']>=0 && !isset($_GET['NOLOHVisit'])))
			{
				if(isset($_SERVER['HTTP_REMOTE_SCRIPTING']) || isset($_POST['NOLOHServerEvent']) || !isset($_SESSION['NOLOHVisit']) || isset($_GET['NWidth']))
					self::Reset(false, false);
				self::UnsetNolohSessionVars();
				self::SetStartUpPage($className, $unsupportedURL, $urlTokenMode, $tokenTrailsExpiration);
				return;
			}
			if(isset($_POST['NoSkeleton']) && GetBrowser()=='ie')
			{
				$srcs = $_SESSION['NOLOHScriptSrcs'];
				self::UnsetNolohSessionVars();
				$this->HandleFirstRun($className, $unsupportedURL, false);
				$_SESSION['NOLOHScriptSrcs'] = $srcs;
				AddScript('NOLOHVisit=-1', Priority::High);
			}
			//set_error_handler('NOLOHErrorHandler');
			//set_exception_handler('NOLOHErrorHandler');
			if(isset($_SESSION['NOLOHOmniscientBeing']))
				$this->TheComingOfTheOmniscientBeing();
			if(!empty($_POST['NOLOHClientChanges']))
				$this->HandleClientChanges();
			if(!empty($_POST['NOLOHFileUploadId']))
				GetComponentById($_POST['NOLOHFileUploadId'])->File = &$_FILES['NOLOHFileUpload'];
			foreach($_SESSION['NOLOHFiles'] as $key => $val)
				GetComponentById($key)->File = new File($val);
			if(!empty($_POST['NOLOHServerEvent']))
				$this->HandleServerEvent();
			foreach($_SESSION['NOLOHFiles'] as $key => $val)
			{
				unlink($_SESSION['NOLOHFiles'][$key]['tmp_name']);
				GetComponentById($key)->File = null;
				unset($_SESSION['NOLOHFiles'][$key]);
			}
			$this->Run();
		}
		else
			$this->HandleFirstRun($className, $unsupportedURL);
	}
	
	static function UnsetNolohSessionVars()
	{
		unset($_SESSION['NOLOHVisit'],
			$_SESSION['NOLOHNumberOfComponents'],
			$_SESSION['NOLOHOmniscientBeing'],
			$_SESSION['NOLOHControlQueue'],
			$_SESSION['NOLOHControlInserts'],
			$_SESSION['NOLOHFunctionQueue'],
			$_SESSION['NOLOHPropertyQueue'],
			$_SESSION['NOLOHScript'],
			$_SESSION['NOLOHScriptSrcs'],
			$_SESSION['NOLOHGlobals'],
			$_SESSION['NOLOHFiles'],
			$_SESSION['NOLOHFileSend'],
			$_SESSION['NOLOHGarbage'],
			$_SESSION['NOLOHStartUpPageClass'],
			$_SESSION['NOLOHURL'],
			$_SESSION['NOLOHTokens'],
			$_SESSION['HighestZIndex'],
			$_SESSION['LowestZIndex']);
	}
	
	private function HandleFirstRun($className, $unsupportedURL, $trulyFirst=true)
	{
		$_SESSION['NOLOHVisit'] = -1;
		$_SESSION['NOLOHNumberOfComponents'] = 0;
		$_SESSION['NOLOHControlQueue'] = array();
		$_SESSION['NOLOHControlInserts'] = array();
		$_SESSION['NOLOHFunctionQueue'] = array();
		$_SESSION['NOLOHPropertyQueue'] = array();
		$_SESSION['NOLOHScript'] = array('', '', '');
		$_SESSION['NOLOHScriptSrcs'] = array();
		$_SESSION['NOLOHGlobals'] = array();
		$_SESSION['NOLOHFiles'] = array();
		$_SESSION['NOLOHFileSend'] = array();
		$_SESSION['NOLOHGarbage'] = array();
		$_SESSION['NOLOHStartUpPageClass'] = $className;
		$_SESSION['NOLOHURL'] = $_SERVER['PHP_SELF'];
		$_SESSION['NOLOHTokens'] = array();
		$_SESSION['HighestZIndex'] = 0;
		$_SESSION['LowestZIndex'] = 0;
		UserAgentDetect::LoadInformation();
		if($trulyFirst)
			if(/*true || */($_SESSION['NOLOHBrowser'] == 'other' && $_SESSION['NOLOHOS'] == 'other'))
				$this->SearchEngineRun();
			else 
				WebPage::SkeletalShow($unsupportedURL);
	}
	
	private function TheComingOfTheOmniscientBeing()
	{
		global $OmniscientBeing;
		$OmniscientBeing = unserialize(defined('FORCE_GZIP') ? gzuncompress($_SESSION['NOLOHOmniscientBeing']) : $_SESSION['NOLOHOmniscientBeing']);
		unset($_SESSION['NOLOHOmniscientBeing']);
		foreach($_SESSION['NOLOHGarbage'] as $id => $nothing)
		{
			$control = &$GLOBALS['OmniscientBeing'][$id];
			if(!isset($_SESSION['NOLOHGarbage'][$control->GetParentId()]) && $control->GetShowStatus()!==0 && $control instanceof Control)
				AddScript("_NAsc('$id')", Priority::Low);
			unset($OmniscientBeing[$id]);
		}
		$_SESSION['NOLOHGarbage'] = array();
		$this->WebPage = GetComponentById($_SESSION['NOLOHStartUpPageId']);
	}
	
	private function HandleClientChanges()
	{
		$GLOBALS['PropertyQueueDisabled'] = true;
		$componentChanges = explode('~d0~', $_POST['NOLOHClientChanges']);
		$numComponents = count($componentChanges);
		for($i = 0; $i < $numComponents; ++$i)
		{
			$changes = explode('~d1~', $componentChanges[$i]);
			$component = &GetComponentById($changes[0]);
			$changeCount = count($changes);
			$j = 0;
			while(++$j < $changeCount)
				$component->{$changes[$j]} = $changes[++$j];
			
			//	switch($changes[++$j])
			//	{
					// Strings
					/*case "ViewMonth":
					case "ViewYear":
					case "Date":
					case "Month":
					case "Year":
					case "Text":
					case "Src":
					case "BackColor":
					case "Color":
					case "ZIndex":
					case "SelectedTab":
						GetComponentById($splitChange[0])->{$splitChange[1]} = $splitChange[2];
						//$runThisString .= $splitChange[1] . ' = "' . $splitChange[2] . '";';
						break;*/
					// Functions
					/*case "KillLater":
						if(GetComponentById($splitChange[0]) != null)
							GetComponentById($splitChange[0])->Close();
							/*$runThisString .= 'Close();';
						else
							$runThisString = "";
						break;*/
					//case "SelectedTab":
					//	$runThisString .= 'SelectedIndex = GetComponentById($splitChange[0])->TabControlBar->Controls->IndexOf(GetComponentById($splitChange[2]));';
						//break;
					// Booleans
					//case "Checked":
					//case "ClientVisible":
						//$runThisString .= $splitChange[1] . ' = ' . $splitChange[2] . ';';
						//break;
					// Explode string to array
				//	case 'Items':
				//		$component->Items = self::ExplodeItems($changes[++$j]);
				//		break;
				//	case 'SelectedIndices':					
				//		$component->SelectedIndices = self::ExplodeSelectedIndices($changes[++$j]);
				//		break;
						//$tmp = strpos($splitChange[1], "->");
						//$runThisString = 'GetComponentById($splitChange[0])->';
						//$runThisString .= $splitChange[1] . ' = $this->Explode' . ($tmp===false?$splitChange[1]:substr($splitChange[1], 0, $tmp)) . '("' . $splitChange[2] . '");';
						//break;
				//	case 'Text':
				//		$component->Text = str_replace('~da~', '&', $changes[++$j]);
				//		break;
				//	default:
						//$runThisString .= $splitChange[1] . ' = ' . $splitChange[2] . ';';
				//		$component->{$changes[$j]} = $changes[++$j];
				//}
			//echo $runThisString;
			//eval($runThisString);
		}
		unset($GLOBALS['PropertyQueueDisabled']);
	}
	
	private function HandleServerEvent()
	{
		if(isset($_POST['NOLOHKey']))
			Event::$Key = $_POST['NOLOHKey'];
		if(isset($_POST['NOLOHCaught']))
			Event::$Caught = $this->ExplodeDragCatch($_POST['NOLOHCaught']);
		Event::$MouseX = $_POST['NOLOHMouseX'];
		Event::$MouseY = $_POST['NOLOHMouseY'];
		$splitEvent = explode('@', $_POST['NOLOHServerEvent']);
		$obj = GetComponentById($splitEvent[1]);
		if($obj != null)
			return $obj->{$splitEvent[0]}->Exec($execClientEvents=false);
		else 
		{
			$splitStr = explode('i', $splitEvent[1], 2);
			return GetComponentById($splitStr[0])->ExecEvent($splitEvent[0], $splitEvent[1]);
		}
	}

	private function HandleTokens()
	{
		if($GLOBALS['NOLOHURLTokenMode'] == 0)
			return;
		unset($_GET['NOLOHVisit'], $_GET['NWidth'], $_GET['NHeight']);
		if($GLOBALS['NOLOHURLTokenMode'] == 1)
			$_SESSION['NOLOHTokens'] = $_GET;
		if($GLOBALS['NOLOHURLTokenMode'] == 2)
		{
			$keys = array_keys($_GET);
			$ubound = count($keys) - 1;
			for($i=0; $i<$ubound; ++$i)
				$_SESSION['NOLOHTokens'][$keys[$i]] = $_GET[$keys[$i]];
			if($_GET[$keys[$ubound]] != '')
				$_SESSION['NOLOHTokens'][$keys[$ubound]] = $_GET[$keys[$ubound]];
			else
			{
				$split = explode('&', base64_decode($keys[$ubound]));
				$count = count($split);
				for($i=0; $i<$count; ++$i)
				{
					$split2 = explode('=', $split[$i].'=');
					$_SESSION['NOLOHTokens'][$split2[0]] = $split2[1];
				}
			}
		}
		$query = explode('?', $_SERVER['REQUEST_URI']);
		if(isset($query[1]) && $query[1]!=$_SERVER['QUERY_STRING'])
		{
			$query = $query[1];
			$split = explode('&', $query);
			$ubound = count($split) - 4;
			for($i=0; $i<$ubound; ++$i)
			{
				$split2 = explode('=', $split[$i]);
				$_SESSION['NOLOHTokens'][$split2[0]] = $split2[1];
			}
			$split2 = explode('=', $split[$ubound]);
			if($GLOBALS['NOLOHURLTokenMode'] == 1 || $split2[1] != '')
				$_SESSION['NOLOHTokens'][$split2[0]] = $split2[1];
			else 
			{
				$split = explode('&', base64_decode($split2[0]));
				$count = count($split);
				for($i=0; $i<$count; ++$i)
				{
					$split2 = explode('=', $split[$i].'=');
					$_SESSION['NOLOHTokens'][$split2[0]] = $split2[1];
				}
			}
		}
	}

	private function Run()
	{
		global $OmniscientBeing;
		if(defined('FORCE_GZIP'))
			ob_start('ob_gzhandler');
			
		if(++$_SESSION['NOLOHVisit']==0)
		{
			$this->HandleTokens();
			$className = $_SESSION['NOLOHStartUpPageClass'];
			$this->WebPage = new $className();
			$_SESSION['NOLOHStartUpPageId'] = $this->WebPage->Id;
			$this->WebPage->Show();
		}
		if(isset($GLOBALS['NOLOHTokenUpdate']) && (!isset($_POST['NoSkeleton']) || GetBrowser()!='ie'))
			URL::UpdateTokens();
		NolohInternal::ShowQueue();
		NolohInternal::FunctionQueue();
		NolohInternal::SetPropertyQueue();
		$sendStr = '/*~NScript~*/' . $_SESSION['NOLOHScript'][0] . $_SESSION['NOLOHScript'][1] . $_SESSION['NOLOHScript'][2];
		print($sendStr);
		$_SESSION['NOLOHScript'] = array('', '', '');
		$_SESSION['NOLOHOmniscientBeing'] = defined('FORCE_GZIP') ? gzcompress(serialize($OmniscientBeing),1) : serialize($OmniscientBeing);
		$GLOBALS['NOLOHGarbage'] = true;
		unset($OmniscientBeing, $GLOBALS['OmniscientBeing']);
		unset($GLOBALS['NOLOHGarbage']);
	}
	
	private function SearchEngineRun()
	{
		$this->HandleTokens();
		$file = getcwd()."/NOLOHSearchTrails.dat";
		if(file_exists($file))
		{
			$tokenString = URL::TokenString($_SESSION['NOLOHTokens']);
			$trails = unserialize(base64_decode(file_get_contents($file)));
			if($trails !== false && isset($trails[$tokenString]))
				foreach($trails[$tokenString] as $key => $nothing)
					print('<a href="' . $_SERVER['PHP_SELF'] . '?' . $key . '">' . $key . '</a> ');
		}
		$className = $_SESSION['NOLOHStartUpPageClass'];
		$this->WebPage = new $className();
		$_SESSION['NOLOHStartUpPageId'] = $this->WebPage->Id;
		$this->WebPage->SearchEngineShow();
		session_destroy();
		session_unset();
	}
	
	private function ExplodeDragCatch($objectsString)
	{
		$objs = array();
		$objectsIdArray = explode(',', $objectsString);
		$objectsCount = count($objectsIdArray);
		for($i=0; $i<$objectsCount; $i++)
			$objs[] = GetComponentById($objectsIdArray[$i]);
		return $objs;
	}
	/*
	private function ExplodeItems($optionsString)
	{
		$items = new ArrayList();
		$optionsArray = explode('~d3~', $optionsString);
		$optionsCount = count($optionsArray);
		for($i=0; $i<$optionsCount; $i++)
		{
			$option = explode('~d2~', $optionsArray[$i]);
			$items->Add(new Item($option[0], $option[1]));
		}
		return $items;
	}
	
	private function ExplodeSelectedIndices($indicesString)
	{
		return explode('~d2~', $indicesString);
	}
	*/
}

?>