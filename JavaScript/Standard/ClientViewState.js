_N.Saved = {};
_N.Changes = {};
_N.EventVars = {};
_N.SEQ = [];
_N.Incubator = {};
_N.IncubatorRoots = {};
_N.IncubatorRootsIns = {};
_N.Visit = -1;
_N.EventDepth = 0;
_N.HighestZ = 0;
_N.LowestZ = 0;
_N.Request = true;
event = null;
function _NInit(configs)
{
	//window.onscroll = _NBodyScrollState;
	window.onresize = _NBodySizeState;
	var key, configDefaults = {"DebugMode": true, "TimeoutTicks": 2760};
	for(key in configs)
	{
		_N[key] = configs[key];
		if(key in configDefaults)
			delete configDefaults[key];
	}
	for(key in configDefaults)
		_N[key] = configDefaults[key];
	_N.Saved[document.body.id] = {};
	_NSet(document.body.id, "Width", document.documentElement.clientWidth);
	_NSet(document.body.id, "Height", document.documentElement.clientHeight);
	_N.WindowWidth = window.outerWidth;
	_N.WindowHeight = window.outerHeight;
	var graveyard = document.createElement("DIV");
	graveyard.id = "NGraveyard";
	graveyard.style.display = "none";
	document.body.appendChild(graveyard);
	_N.TimeoutCount = 0;
	_N.Hash = location.hash;
	_N.URLChecker = setInterval(_NCheckURL, 500);
}
function _NSetLoadIndi(id)
{
	_N.LoadIndicator = id;
	var loadIndicator = _N(id);
	if (loadIndicator)
	{
		loadIndicator.style.visibility = "hidden";
		loadIndicator.className += " loading-indicator";
	}
}
function _NCheckURL()
{
	if(_N.Hash != location.hash && (location.hash=="" || location.hash.charAt(1)=="/") && (_N.Hash=="" || _N.Hash.charAt(1)=="/"))
		location.reload(true);
	if(++ _N.TimeoutCount == _N.TimeoutTicks)
	{
		var serverArg = _N.TimeoutDuration ? _NTimeoutTicker() : "Ping";
		_NXHR("POST", location.href.toString().replace(location.hash, ""), null, true).send("_NTimeout="+serverArg);
		_N.TimeoutCount = 0;
	}
}
function _NSetURL(url, id)
{
	_N.URLTokenLink = id;
	location = url;
	_N.Hash = location.hash;
	if(_N.Tracker)
		eval(_N.Tracker);
}
function _NSetTokens(hash, id)
{
	_NSetURL(document.URL.split("#",1)[0] + "#!/" + hash, id);
}
function _NSet(id, property, value, force)
{
	_NChangeByObj(_N(id), property, value);
	_NSave(id, property, value);
	if (force)
	{
		_N.Saved[id][property] = !value;
	}
	return value;
}
function _NSetProperty(id, property, value)	{return _NSet(id, property, value)}
function _NChange(id, property, value)
{
	_NChangeByObj(_N(id), property, value);
}
function _NChangeByObj(obj, property, value)
{
	if(obj)
		switch(property)
		{
			case "onclick":
				obj.onclick = _NEvent("if(!_N.DisableClicks && (!event || event.button!=2)) {" + value + "}", obj);
				obj.className = value ? "NClickable " + obj.className : obj.className.replace(/NClickable/g, "");
				break;
			case "KeyPress":
			case "ReturnKey":
			case "TypePause":
				obj.onkeypress = _NEvent("_NKeyEvntsPress.call(obj);", obj);
			case "onblur":
			case "onchange":
			case "ondblclick":
			case "onfocus":
			case "onelapsed":
			case "oninput":
			case "onmouseover":
			case "onload":
			case "onpaste":
			case "onscroll":
			case "onunload":
			case "Select":
			case "Deselect":
				obj[property] = _NEvent(value, obj);
				break;
			case "onmouseup":
				obj.onmouseup = _NEvent("if(!event || event.button!=2) {" + value + "}", obj);
				break;
			case "oncontextmenu":
				obj.oncontextmenu = _NEvent(value + "; if(obj.ContextMenu) _NCMShow(obj); return false;", obj);
				break;
			case "onmousedown":
			case "ontouchstart":
				obj[property] = _NEvent("if(!event || event.button!=2) {" + value + "; if(obj.Shifts && obj.Shifts.length && !_N.Shifts) _NShftSta(obj.Shifts);}", obj);
				break;
			case "onmouseout":
				obj.onmouseout = _NEvent("var to = event.relatedTarget, runWhile = true; try{to;to.id;} catch(err) {runWhile=false;} if(runWhile) while(to && to.tag!='BODY') {if(to.id == obj.id) return; to = to.parentNode;} " + value, obj);
				break;
			case "DragCatch":
				if(value != "")
					_N.Catchers.push(obj.id);
				else
					for(var i=0; i<_N.Catchers.length; ++i)
						if(_N.Catchers[i] == obj.id)
						{
							_N.Catchers.splice(i, 1);
							break;
						}
				obj.DragCatch = _NEvent(value, obj);
				break;
			case "href":
				obj.href = value=="#" ? "javascript:void(0);" : value;
				break;
			case "Shifts":
				_NSetShifts(obj);
			case "ChildrenArray":
				eval("obj." + property + " = " + value + ";");
				break;
			case "ContextMenu":
				if (!value) {
					obj.oncontextmenu = null;
				} else if(!obj.oncontextmenu) {
					_NChangeByObj(obj, "oncontextmenu", "");
				}
				obj.ContextMenu = value;
				break;
			case "GroupM":
				obj._NMultiGroupable = true;
			case "Group":
				if(obj.Group = _N(value))
					obj.Group.Elements.push(obj.id);
				break;
			case "Selected":
				if(obj.Selected==true != value)
				{
					if(obj.Group)
					{
						var selId = obj.Group.GetSelectedElement(), selEle;
						if((value && selId) || selId==obj.id)
							obj.Group.PrevSelectedElement = selId;
						if(selId && value && !obj._NMultiGroupable && (selEle = _N(selId)) && !selEle._NMultiGroupable)
						{
							_NSave(selId,"Selected",selEle.Selected=false);
							if(selEle.Deselect && _N.QueueDisabled!=obj.id)
								selEle.Deselect();
						}
					}
					_NSave(obj.id,"Selected",obj.Selected=value);
					if(value)
					{
						if(obj.Select)
							obj.Select();
					}
					else
						if(obj.Deselect)
							obj.Deselect();
					if(obj.Group && obj.Group.onchange && _N.QueueDisabled!=obj.id)
						obj.Group.onchange();
				}
				else if(event)
				{
					event.preventDefault();
					_NNoBubble();
				}
				break;
			case "style.zIndex":
				if(value > _N.HighestZ)
					_N.HighestZ = value;
				if(value < _N.LowestZ)
					_N.LowestZ = value;
				obj.style.zIndex = (obj.Buoyant)?value + 9999 : value;
				break;
			case "style.left":
				if(obj.Buoyant)
				{
					obj.Buoyant.Left = parseInt(value);
					_NByntMv(obj.id);
				}
				else
				{
					obj.style.left = value;
					if(obj.BuoyantChildren)
						_NByntMvCh(obj);
				}
				break;
			case "style.top":
//				if(obj.BuoyantParentId)
				if(obj.Buoyant)
				{
					obj.Buoyant.Top = parseInt(value);
					_NByntMv(obj.id);
				}
				else
				{
					obj.style.top = value;
					if(obj.BuoyantChildren)
						_NByntMvCh(obj);
				}
				break;
			case "style.display":
				obj.style.display = value;
				if(obj.BuoyantChildren && !value)
					_NByntMvCh(obj);
				break;
			case "className":
				obj.className = obj.className.indexOf("NClickable")!=-1 && value.indexOf("NClickable")==-1 ? "NClickable " + value : value;
				break;
			case "value":
				obj.value = value;

                if (!obj.classList.contains('disable-dispatch-input'))
                {
                    obj.dispatchEvent(_NCreateEvent('input'));
                }
				break;
			default:
				eval("obj." + property + " = value;");
		}
	return value;
}

function _NEvent(code, obj)
{
	var id = typeof obj == "object" ? obj.id : obj;
	eval("var func = function(e) {if(_N.QueueDisabled!='"+id+"') {if(e) event=e; var liq=(event && event.target.id=='"+id+"'); ++_N.EventDepth; try {" + code + ";} catch(err) {_NAlertError(err);} finally {if(!--_N.EventDepth) if(_N.SEQ.length) window.setTimeout(function() {if(_N.Uploads && _N.Uploads.length) _NServerWUpl(); else _NServer(); event=null;}, 0); else event=null;}}}");
	return func;
}
function _NCreateEvent(eventType)
{
	var event;
	if (typeof(Event) === 'function') {
		event = new Event(eventType, {bubbles: true, cancelable: true});
	} else {
		event = document.createEvent('Event');
		event.initEvent(eventType, true, true);
	}

	return event;
}
function _NNoBubble()
{
	if(event)
		event.stopPropagation();
}
function _NSave(id, property, value)
{
	if(id.indexOf("_") >= 0 || id==_N.QueueDisabled)
		return;
	var obj = _N(id);
	if(typeof value == "undefined")
		eval("value = obj."+property+";");
	if(!_N.Changes[id])
		_N.Changes[id] = {};
	switch(property)
	{
		case "value":
			_N.Changes[id][property] = typeof value == "string" ? value.replace(/&/g, "~da~").replace(/\+/g, "~dp~") : value;
			break;
		case "style.left":
		case "style.top":
		case "style.width":
		case "style.height":
			_N.Changes[id][property] = parseInt(value);
			break;
		case "style.visibility":
		case "style.display":
			var obj = _N(id);
			_N.Changes[id]["Visible"] = obj.style.display=="none" ? "null" : (obj.style.visibility == "inherit" ? "Cloak" : false);
			break;
		case "style.opacity":
			_N.Changes[id]["Opacity"] = value * 100;
			break;
		default:
			_N.Changes[id][property] = (typeof value == "boolean") ? (value ? 1 : 0) : ((typeof value == "object") ? JSON.stringify(value) : value);
	}
}
/*
function _NBodyScrollState()
{
	var x = Math.max(document.body.scrollLeft, document.documentElement.scrollLeft),
		 y = Math.max(document.body.scrollTop, document.documentElement.scrollTop),
		 loadIndicator = _N(_N.LoadIndicator);
	loadIndicator.style.left = x+7+"px";
	loadIndicator.style.top = y+7+"px";
}
*/
function _NBodySizeState()
{
	var body = document.body;
	if(body.ShiftsWith)
		_NShiftWithBody(body);
	if(body.BuoyantChildren)
		_NByntMvCh(body);
	_NSet(body.id, "Width", document.documentElement.clientWidth);
	_NSet(body.id, "Height", document.documentElement.clientHeight);
	_N.WindowWidth = window.outerWidth;
	_N.WindowHeight = window.outerHeight;
}
function _NSetP(id, nameValuePairs)
{
	_N.QueueDisabled = id;
	var i = -1, obj = _N(id), count = nameValuePairs.length, cachedSave = _N.Saved[id];
	while(++i<count)
		cachedSave[nameValuePairs[i]] = _NChangeByObj(obj, nameValuePairs[i], nameValuePairs[++i]);
	delete _N.QueueDisabled;
}
function _NQ()
{
	var addTo, id, info, roots = _N.IncubatorRoots;
	for(addTo in roots)
	{
		var obj = _N(addTo);
		if (!obj)
		{
			debugger;
		}
		obj.appendChild(roots[addTo]);
	}
	for(id in _N.IncubatorRootsIns)
	{
		info = _N.IncubatorRootsIns[id];
		_NAddAct(_N.Incubator[id], info[0], info[1]);
	}
	_N.Incubator = {};
	_N.IncubatorRoots = {};
	_N.IncubatorRootsIns = {};
}
function _NAddAct(ele, addTo, beforeId)
{
	addTo = _N(addTo);
	if(!beforeId)
		addTo.appendChild(ele);
	else
	{
		var before = _N(beforeId);
		if(before && before.parentNode == addTo)
			addTo.insertBefore(ele, before);
		else
			addTo.appendChild(ele);
	}
}
function _NAdd(addTo, tag, id, nameValuePairs, beforeId)
{
	_N.QueueDisabled = id;
	var ele = document.createElement(tag), count = nameValuePairs.length, i=-1, cachedSave;
	ele.style.position = "absolute";
	cachedSave = _N.Saved[ele.id = id] = {};
	while(++i<count)
		cachedSave[nameValuePairs[i]] = _NChangeByObj(ele, nameValuePairs[i], nameValuePairs[++i]);
	_N.Incubator[id] = ele;
	if(_N.Incubator[addTo] || addTo == "NHead")
		_NAddAct(ele, addTo, beforeId);
	else if(beforeId)
		_N.IncubatorRootsIns[id] = [addTo, beforeId];
	else
	{
		if(!_N.IncubatorRoots[addTo])
			_N.IncubatorRoots[addTo] = document.createDocumentFragment();
		_N.IncubatorRoots[addTo].appendChild(ele);
	}
	delete _N.QueueDisabled;
}
function _NAdopt(id, parentId)
{
    var ele = _N(id);
	if(_N.Incubator[parentId])
	{
		_N.Incubator[id] = _N(id);
		_N.IncubatorRootsIns[id] = [parentId, null];
	}
	else
	{
		ele.parentNode.removeChild(ele);
		_N(parentId).appendChild(ele);
	}
}
function _NRem(id)
{
	var ele = _N(id);
	if(ele)
	{
		ele.parentNode.removeChild(ele);
		_N("NGraveyard").appendChild(ele);
		if(ele.BuoyantChildren)
			for(var i=0; i<ele.BuoyantChildren.length; ++i)
				_NRem(ele.BuoyantChildren[i]);
		if(ele.NonControls)
			for(var i=0; i<ele.NonControls.length; ++i)
				_N(ele.NonControls[i]).Stop();
	}
}
function _NRes(id, parentId)
{
	var ele = _N(id);
	_N("NGraveyard").removeChild(ele);
	_N(parentId).appendChild(ele);
    if(ele.BuoyantChildren)
    	for(var i=0; i<ele.BuoyantChildren.length; ++i)
			_NRes(ele.BuoyantChildren[i], parentId);
	if(ele.NonControls)
	    for(var i=0; i<ele.NonControls.length; ++i)
			_N(ele.NonControls[i]).Start();
}
function _NAsc(id)
{
	var ele = _N(id);
	if(ele)
    {
    	if(ele.Buoyant)
    		_NByntFrgt(id, _N(ele.Buoyant.ParentId));
        if(ele.BuoyantChildren)
        	for(var i=0; i<ele.BuoyantChildren.length; ++i)
        	{
        		//_NAsc(ele.BuoyantChildren[i]);
        		//var parent = _N(_N(ele.BuoyantChildren[i]).BuoyantParentId);
        		var parent = ele.parentNode;
        		_NByntFrgt(ele.BuoyantChildren[i], parent);
        		_NAsc(ele.BuoyantChildren[i]);
        		
        	}
		if(ele.NonControls)
			for(var i=0; i<ele.NonControls.length; ++i)
				_N(ele.NonControls[i]).Destroy();
        ele.parentNode.removeChild(ele);
    }
}
function _NGCAsc(idArr)
{
	var arrLength = idArr.length;
	for(var i=0; i<arrLength; ++i)
		_NAsc(idArr[i]);
}
function _NChangeString()
{
	if(_N.Observes)
		_NObserveSave();
	var change = "", changes = "", id, property;
	for(id in _N.Changes)
	{
		change = id;
		for(property in _N.Changes[id])
			if(_N.Changes[id][property] != _N.Saved[id][property])
			{
				change += "~d1~" + property.replace("style.", "") + "~d1~" + _N.Changes[id][property];
				_N.Saved[id][property] = _N.Changes[id][property];
			}
		if(change != id)
			changes += change + "~d0~";
	}
	_N.Changes = {};

	changes = changes
		.substring(0, changes.length - 4)
		.replace("&", "%26");

	return changes;
}
function _NEventVarsString()
{
	var key, str = "", id;
	if(id = _N.EventVars.FocusedComponent)
	{
		var obj = _N(id), selText;
		_NSave(id, "value");
        try
		{
			str += "FocusedComponent~d0~"+id+"~d0~";
			if(selText = obj.value.substring(obj.selectionStart, obj.selectionEnd))
				str += "SelectedText~d0~"+selText+"~d0~";
		}
		catch(err)	{}
		finally
		{
			delete _N.EventVars.FocusedComponent;
		}
	}
	for(key in _N.EventVars)
		str += key + "~d0~" + (typeof _N.EventVars[key] == "object" ? _N.EventVars[key].join(",") : _N.EventVars[key]) + "~d0~";
	_N.EventVars = {};
	return str.substring(0, str.length-4);
}
function _NProcessResponse(text)
{
	var pos = text.indexOf("/*_N*/"), response = [text.substring(0, pos), text.substring(pos)];
	if(response[0] != "")
	{
		var s = document.createElement("SCRIPT");
		s.type = "text/javascript";
		s.text = response[0];
		document.getElementsByTagName("head")[0].appendChild(s);
		eval(response[0]);
	}
	if(_N.DebugMode == "Full")
		_NDebugFull(response[1]);
	else
		eval(response[1]);
}
function _NAlertError(err)
{
	alert(_N.DebugMode ? "A javascript error has occurred:\n\n" + err.name + ": " + err.message : "An application error has occurred.");
}
function _NUnServer(loadIndicator)
{
	_N.TimeoutCount = 0;
	if(_N.TimeoutWorking)
		_N.TimeoutWorking = null;
	_N.LoadIndicator = loadIndicator;
	_N(loadIndicator).style.visibility = "hidden";
	_N.Spinner = null;
	_N.Request = null;
	if(_N.Listeners)
		_NListenersCont();
}
function _NReqStateChange()
{
	var req = _N.Request, loadIndicator = _N.LoadIndicator;
	if(req.readyState == 4)
	{
		if(req.status == 200)
		{
			var text = _N.Request.responseText;
			if(_N.DebugMode == null)
			{
				_NProcessResponse(text);
			}
			else
			{
				try
				{
					_NProcessResponse(text);
				}
				catch(err)
				{
					var el = document.createElement("DIV");
					el.innerHTML = text;
					text = el.textContent;
					var matches = text.match(/(.*): (.*) in (.*) on line ([0-9]+)/);
					if(matches)
						alert(matches[1] + matches[2] + "\nin " + matches[3] + "\non line " + matches[4]);
					else
						_NAlertError(err);
				}
			}
		}
		else if (req.status == 0)
		{
			alert("Unable to contact server. Please check your connection.");
		}
		else
		{
			alert("HTTP error: " + req.status + "\n" + req.statusText);
		}

		_NUnServer(loadIndicator);
	}
}
function _NSE(eventType, id, uploads)
{
	if(_N.Listeners)
		_NListenersHold();
	if(!_N.EventVars.MouseX && event)
	{
		_N.EventVars.MouseX = event.pageX;
		_N.EventVars.MouseY = event.pageY;
	}
	if(_N.SEQ.Started != null)
	{
		for(var i=_N.SEQ.Started; i<_N.SEQ.length; ++i)
			if(_N.SEQ[i][0] == eventType && _N.SEQ[i][1] == id)
				_N.SEQ.splice(i--, 1);
	}
	else
		_N.SEQ.Started = _N.SEQ.length;
	_N.SEQ.push([eventType, id]);
	if(uploads)
		_N.Uploads = _N.Uploads.concat(uploads);
	if(eventType == "Unload")
		_NServer();
}
function _NServer()
{
	if(!_N.Request)
	{
		var url = location.href, hashPos = url.indexOf("#!/"), queryPos, notUnload = true, sECount = _N.SEQ.length;
		var str = "_NVisit="+ ++_N.Visit+"&_NEventVars="+_NEventVarsString()+"&_NChanges="+_NChangeString()+"&_NEvents=";
		for(var i=0; i<sECount; ++i)
		{
			if(_N.SEQ[i][0] == "Unload")
				notUnload = false;
			str += _N.SEQ[i][0] + "@" + _N.SEQ[i][1] + ",";
		}
		_N.SEQ = [];
		str = str.substr(0, str.length-1);
		if(_N.URLTokenLink)
		{
			str += "&_NTokenLink="+_N.URLTokenLink;
			_N.URLTokenLink = null;
		}
		if (_N.Spinner !== false)
		{
			_N(_N.LoadIndicator).style.visibility = "visible";
		}
		_N.Request = _NXHR("POST", 
	    	hashPos==-1 ? url.replace(location.hash,"") : url.replace("#!/",(queryPos=url.indexOf("?"))==-1||hashPos<queryPos?"?":"&"),
	    	notUnload ? _NReqStateChange : null,
	    	notUnload);
		_N.Request.send(str);
	}
}