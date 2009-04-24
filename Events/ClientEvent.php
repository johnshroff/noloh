<?php
/**
 * ClientEvent class
 *
 * A ClientEvent is a kind of Event that is executed on the client without communicating with the server.<br>
 * As a ClientEvent is more responsive than a {@see ServerEvent}, they are useful for running events that make the simplest changes<br>
 * to the visual aspects of an application. In order to enable client viewstate with your own ClientEvents, however, requires calling<br>
 * special NOLOH JavaScript functions. {@see JavaScriptFunctions}
 * 
 * <pre>
 * 	// Instantiates a new Button
 *  $btn = new Button("Click Me");
 * 	// Sets the click of the button to an event which will alert without going to the server.
 * 	$btn->Click = new ClientEvent('alert("I have been clicked");');
 * 	// Launches that event. In particular, it will alert.
 *  $btn->Click->Exec();
 * </pre>
 * 
 * For more information, please see
 * @link /Tutorials/Events.html#ClientEvents
 * 
 * @package Events
 */
class ClientEvent extends Event
{
	/**
	 * @ignore
	 */
	static function GenerateString($eventType, $str)
	{
		return str_replace('\'', '\\\'', $str);
	}
	/**
	 * Constructor.
	 * @param string $allCodeAsString Either the full JavaScript code to be executed or the name of a JavaScript function as a string
	 * @param mixed,... $params the optional params to be passed to your JavaScript function 
	 */
	function ClientEvent($allCodeAsString, $params=null)
	{
		if($allCodeAsString !== '' && !preg_match('/(?:;|})\s*?\z/', $allCodeAsString))
		{
			$allCodeAsString = trim($allCodeAsString) . '(';
			$params = func_get_args();
			$count = count($params);
			for($i=1;$i<$count;++$i)
				$allCodeAsString .= self::ClientFormat($params[$i]) .',';
			$allCodeAsString = rtrim($allCodeAsString, ',') . ');';
			parent::Event($allCodeAsString);
		}
		else
			parent::Event(str_replace("\n", ' ', $allCodeAsString));
	}
	/**
	 * @ignore
	 */
	static public function ClientFormat($param)
	{
		if(is_string($param))
			return '"'.str_replace(array('"', "\n"), array('\\\\"', ' '), $param).'"';
		elseif(is_int($param) || is_float($param))
			return $param;
		elseif(is_bool($param))
			return ($param?'true':'false');
		elseif($param === null)
			return 'null';
		elseif(is_array($param))
		{
			$tmpArr = array();
			foreach($param as $val)
				$tmpArr[] = self::ClientFormat($val);
			return '[' . implode(',', $tmpArr) . ']';
		}
		elseif($param instanceof Component)
			return '"' . $param->Id . '"';
		elseif(is_object($param))
			BloodyMurder('Objects can not be passed as parameters to ClientEvent');
	}
	/**
	 * @ignore
	 */
	function GetInfo(&$arr, &$onlyClientEvents, $parentLiquid = false, &$liquidClientEvent = '')
	{
		if($onlyClientEvents)
			if($parentLiquid)
				$liquidClientEvent .= $this->ExecuteFunction;
			elseif($this->Liquid)
				$arr[0] .= 'if(liq){' . $this->ExecuteFunction . '} ';
			else
				$arr[0] .= $this->ExecuteFunction;
		if($this->Bubbles === false)
			$arr[4] = false;
		return $arr;
	}
	/**
	 * @ignore
	 */
	function GetEventString($eventType, $objsId)
	{
		if($this->GetEnabled())
		{
			$txt = ClientEvent::GenerateString($eventType, $this->Liquid?'if(liq){'.$this->ExecuteFunction.'}':$this->ExecuteFunction);
			if($this->Bubbles === false)
				$txt .= '_NNoBubble();';
			return $txt;
		}
		return '';
	}
	/**
	 * @ignore
	 */
	function Blank()
	{
		return false;
	}
	/*
	 * @ignore
	 *
	function ParseToJS($eventTypeAsString, $ObjsId)
	{
		//Temporary solution until it parses
		if(is_string($this->ExecuteFunction))
			//return addslashes(str_replace("'", stripslashes("\""), $this->ExecuteFunction));
			return $this->ExecuteFunction;
		elseif($this->ExecuteFunction instanceof ArrayList)
		{ 
			$Code = '';
			$EventCount = $this->ExecuteFunction->Count();
			for($i=0; $i<$EventCount; $i++)
				if(get_class($this->ExecuteFunction[$i]) == 'ServerEvent')
					$Code .= $this->ExecuteFunction[$i]->GetEventString($eventTypeAsString."->ExecuteFunction[$i]", $ObjsId);
				elseif(get_class($this->ExecuteFunction[$i]) == 'ClientEvent')
					$Code .= $this->ExecuteFunction[$i]->ParseToJS($eventTypeAsString."->ExecuteFunction[$i]", $ObjsId);
			return $Code;
		}
	}*/
	
	/**
	 * Launches the particular event. That is, the client will be notified to execute the given JavaScript.
	 * @param boolean $execClientEvents Indicates whether client-side code will execute. <br>
	 * <b>Note</b>:Modifying this parameter is highly discouraged as it may lead to unintended behavior.<br>
	 */
	function Exec(&$execClientEvents=true)
	{
		if(!$GLOBALS['_NQueueDisabled'] && $execClientEvents && $this->Enabled===null)
			if(!isset($GLOBALS['_NClientEventExecs']))
				$GLOBALS['_NClientEventExecs'] = array($this);
			else
				$GLOBALS['_NClientEventExecs'][] = $this;
	}
	/**
	 * @ignore
	 */
	function AddToScript()
	{
		AddScript(str_replace('\\\'', '\'', $this->ExecuteFunction), Priority::Low);
	}
}

?>