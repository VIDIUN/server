/**
 * Generates a URL-encoded query string from the associative (or indexed) array provided.
 * Ported from PHP. 
 * @param formdata			May be an array or object containing properties. 
 * @param numeric_prefix	If numeric indices are used in the base array and this parameter is provided, it will be prepended to the numeric index for elements in the base array only. 
 * @param arg_separator		arg_separator.output  is used to separate arguments, unless this parameter is specified, and is then used. 
 * @return	Returns a URL-encoded string. 
 */
function http_build_query (formdata, numeric_prefix, arg_separator) {
    var value, key, tmp = [];
    var _http_build_query_helper = function (key, val, arg_separator) {
        var v, tmp = [];
		if (val === true) {
            val = "1";
        } else if (val === false) {
            val = "0";
        }
		if (val !== null && typeof(val) === "object") {
            for (v in val) {
                if (val[v] !== null) {
                    tmp.push(_http_build_query_helper(key + "[" + v + "]", val[v], arg_separator));
                }
			}
            return tmp.join(arg_separator);
        } else if (typeof(val) !== "function") {
            return key + "=" + encodeURIComponent(val);
        } else { 
        	//throw new Error('There was an error processing for http_build_query().');
        	return '';
        }
    };
 
    if (!arg_separator) {
		arg_separator = "&";
    }
    for (key in formdata) {
        value = formdata[key];
        if (numeric_prefix && !isNaN(key)) {
			key = String(numeric_prefix) + key;
        }
        tmp.push(_http_build_query_helper(key, value, arg_separator));
    }
    return tmp.join(arg_separator);
}

/**
 * This will only return (a string) if the object passed to getFunctionName is a function or an "object" function from IE. 
 * The function does not rely on function.name if present as it can't always be trusted.
 * @param func	The function to test.
 * @return string the function name.
 */
function getFunctionName(func) {
  if ( typeof func == "function" || typeof func == "object" )
  var fName = (""+func).match(/^function\s*([\w\$]*)\s*\(/); 
  if ( fName !== null ) 
	  	return fName[1];
  return null;
}

/**
 * Getting the name of the constructor if the constructor hasn't been modified, 
 * which if it has modified (and is therfor invalid to use), it falls back to using Object.prototype.toString 
 * to get the class though it won't return the name of the constructor function that created it then. 
 * If you absolutely need the constructor's name, pass true as the second argument, 
 * and it will reset the constructor if it has been modified, to get the real constructor.
 * @param obj	The object to get the constructor of.
 * @param forceConstructor	preform a deep lookup for the real constructor.
 * @return	The constructor of the given class.
 */
function getClass(obj, forceConstructor) {
  if ( typeof obj == "undefined" ) return "undefined";
  if ( obj === null ) return "null";
  if ( forceConstructor == true && obj.hasOwnProperty("constructor") ) delete obj.constructor; // reset constructor
  if ( forceConstructor != false && !obj.hasOwnProperty("constructor") ) return getFunctionName(obj.constructor);
  return Object.prototype.toString.call(obj)
    .match(/^\[object\s(.*)\]$/)[1];
}

/**
 * validate a paramter's value is not null, if not null, add the parameter to the collection.
 * @param	params		the collection of parameters to send in a service action request.
 * @param	paramName	the new parameter name to add.
 * @param	paramValue	the new parameter value to add.
 */
function addIfNotNull(obj, params, paramName, paramValue)
{
	if (paramValue != null) {
		if(paramValue instanceof VidiunObjectBase) {
			params[paramName] = paramValue.toParams();
		} else {
			params[paramName] = paramValue;
		}
	}
}

/**
 * Serializes new object's parameters.
 * @param obj	The object who's members to serialize.
 * @return		a serialized object.
 */
function toParams(obj)
{
	var params = new Object();
	params["objectType"] = getClass(obj);
    for(var prop in obj) {
    	var val = obj[prop];
    	addIfNotNull(obj, params, prop, val);
	}
	return params;
}

/**
 * Utility global method for extending javascript for allowing easier Inheritance.
 * This method should be called directly after defining the class or object, before extending it's prototype. 
 * @param parentClassOrObject		the parent class or object to inherit from.
 * @return	the object or class being created (the child class).
 */
Function.prototype.inheritsFrom = function( parentClassOrObject ){ 
	if ( parentClassOrObject.constructor == Function ) 
	{ 
		//Normal Inheritance 
		this.prototype = new parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parentClass = parentClassOrObject.prototype;
	} 
	else 
	{ 
		//Pure Virtual Inheritance 
		this.prototype = parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parentClass = parentClassOrObject;
	} 
	return this;
}

/**
 * Sorts an array by key, maintaining key to data correlations. This is useful mainly for associative arrays. 
 * @param arr 	The array to sort.
 * @return		The sorted array.
 */
function ksort(arr) {
  var sArr = [];
  var tArr = [];
  var n = 0;
  for (i in arr)
    tArr[n++] = i+"|"+arr[i];
  tArr = tArr.sort();
  for (var i=0; i<tArr.length; i++) {
    var x = tArr[i].split("|");
    sArr[x[0]] = x[1];
  }
  return sArr;
}

/**
 * Construct new Vidiun service action call, if params array contain sub-arrays (for objects), it will be flattened.
 * @param string	service		The Vidiun service to use.
 * @param string	action			The service action to execute.
 * @param array		params			The parameters to pass to the service action.
 * @param array 	files			Files to upload or manipulate.
 */
function VidiunServiceActionCall(service, action, params, files)
{
	if(!params)
		params = new Object();
	if(!files)
		files = new Object();

	this.service = service;
	this.action = action;
	this.params = this.parseParams(params);
	this.files = files;
}
/**
 * @param string	service		The Vidiun service to use.
 */
VidiunServiceActionCall.prototype.service = null;
/**
 * @param string	action			The service action to execute.
 */
VidiunServiceActionCall.prototype.action = null;
/**
 * @param array		params			The parameters to pass to the service action.
 */
VidiunServiceActionCall.prototype.params = null;
/**
 * @param array 	files			Files to upload or manipulate.
 */
VidiunServiceActionCall.prototype.files = null;
/**
 * Parse params array and sub arrays (clone objects)
 * @param array params	the object to clone.
 * @return the newly cloned object from the input object.
 */
VidiunServiceActionCall.prototype.parseParams = function(params)
{
	var newParams = new Object();
	for(var key in params) {
		var val = params[key];
		if (typeof(val) == 'object') {
			newParams[key] = this.parseParams(val);
		} else {
			newParams[key] = val;
		}
	}
	return newParams;
};

/**
 * Create params object for a multirequest call.
 * @param int multiRequestIndex		the index of the call inside the multirequest.
 */
VidiunServiceActionCall.prototype.getParamsForMultiRequest = function(multiRequestIndex)
{
	var multiRequestParams = new Object();
	multiRequestParams[multiRequestIndex + ":service"] = this.service;
	multiRequestParams[multiRequestIndex + ":action"] = this.action;
	for(var key in this.params) {
		var val = this.params[key];
		multiRequestParams[multiRequestIndex + ":" + key] = val;
	}
	return multiRequestParams;
};

/**
 * Implement to get Vidiun Client logs
 * 
 */
function IVidiunLogger() 
{
}
IVidiunLogger.prototype.log = function(msg){
	if (console.log){
		console.log(msg);
	}
};

/**
 * Vidiun client constructor
 * 
 */
function VidiunClientBase()
{
}

/**
 * Vidiun client init
 * @param VidiunConfiguration config
 */
VidiunClientBase.prototype.init = function(config)
{
    this.config = config;
    var logger = this.config.getLogger();
	if (logger) {
		this.shouldLog = true;	
	}
};

VidiunClientBase.prototype.VIDIUN_API_VERSION = "3.0";
VidiunClientBase.prototype.VIDIUN_SERVICE_FORMAT_JSON = 1;
VidiunClientBase.prototype.VIDIUN_SERVICE_FORMAT_XML = 2;
VidiunClientBase.prototype.VIDIUN_SERVICE_FORMAT_PHP = 3;
VidiunClientBase.prototype.VIDIUN_SERVICE_FORMAT_JSONP = 9;
/**
 * @param VidiunConfiguration The Vidiun Client - this is the facade through which all service actions should be called.
 */
VidiunClientBase.prototype.config = null;
	
/**
 * @param string	the Vidiun session to use.
 */
VidiunClientBase.prototype.vs = null;
	
/**
 * @param boolean	should the client log all actions.
 */
VidiunClientBase.prototype.shouldLog = false;
	
/**
 * @param boolean	should the call be multirequest (set to true when creating multirequest calls).
 */
VidiunClientBase.prototype.useMultiRequest = false;
	
/**
 * @param Array 	queue of service action calls.
 */
VidiunClientBase.prototype.callsQueue = new Array();

/**
 * prepare a call for service action (queue the call and wait for doQueue).
 */
VidiunClientBase.prototype.queueServiceActionCall = function (service, action, params, files)
{
	// in start session partner id is optional (default -1). if partner id was not set, use the one in the config
	if (!params.hasOwnProperty("partnerId") || params["partnerId"] == -1)
		params["partnerId"] = this.config.partnerId;
	this.addParam(params, "vs", this.vs);
	var call = new VidiunServiceActionCall(service, action, params, files);
	this.callsQueue.push(call);
};

/**
 * executes the actions queue.
 */
VidiunClientBase.prototype.doQueue = function(callback)
{
	if (this.callsQueue.length == 0)
		return null;
	var params = new Object();
	var files = new Object();
	this.log("service url: [" + this.config.serviceUrl + "]");
	// append the basic params
	this.addParam(params, "apiVersion", this.VIDIUN_API_VERSION);
	this.addParam(params, "format", this.config.format);
	this.addParam(params, "clientTag", this.config.clientTag);
	var url = this.config.serviceUrl + this.config.serviceBase;
	var call = null;
	if (this.useMultiRequest){
		url += "multirequest";
		$i = 1;
		for(var v in this.callsQueue){
			call = this.callsQueue[v];
			var callParams = call.getParamsForMultiRequest($i++);
			for(var sv1 in callParams)
				params[sv1] = callParams[sv1];

			for(var sv2 in call.files)
				files[sv2] = call.files[sv2];
		}
	} else {
		call = this.callsQueue[0];
		url += call.service + "&action=" + call.action;
		for(var sv3 in call.params)
			params[sv3] = call.params[sv3];
		for(var sv4 in call.files)
			files[sv4] = call.files[sv4];
	}
	// reset
	this.callsQueue = new Array();
	this.useMultiRequest = false; 
	var signature = this.signature(params);
	this.addParam(params, "vidsig", signature);
	this.doHttpRequest(callback, url, params, files);
	return true;
};

/**
 * Sign array of parameters for requests validation (CRC).
 * @param array params		service action call parameters that will be sent on the request.
 * @return string			a hashed signed signature that can identify the sent request parameters.
 */
VidiunClientBase.prototype.signature = function(params)
{
	params = ksort(params);
	var str = "";
	for(var v in params) {
		var v = params[v];
		str += v + v;
	}
	return MD5(str);
};

/**
 * send the http request.
 * @param string url						the url to call.
 * @param parameters params					the parameters to pass.
 * @return array 							the results and errors inside an array.
 */
VidiunClientBase.prototype.doHttpRequest = function (callCompletedCallback, url, params, files)
{
	url += '&' + http_build_query(params);
	OX.AJAST.call(url, "callback", callCompletedCallback, 5000, false);
};

/**
 * getter for the Vidiun session.
 * @return string	VS
 */
VidiunClientBase.prototype.getVs = function()
{
	return this.vs;
};

/**
 * @param string vs	setter for the Vidiun session.
 */
VidiunClientBase.prototype.setVs = function(vs)
{
	this.vs = vs;
};

/**
 * getter for the referenced configuration object. 
 * @return VidiunConfiguration
 */
VidiunClientBase.prototype.getConfig = function()
{
	return this.config;
};

/**
 * @param VidiunConfiguration config	setter for the referenced configuration object.
 */
VidiunClientBase.prototype.setConfig = function(config)
{
	this.config = config;
	logger = this.config.getLogger();
	if (logger instanceof IVidiunLogger){
		this.shouldLog = true;	
	}
};

/**
 * Add parameter to array of parameters that is passed by reference
 * @param array params			array of parameters to pass to a call.
 * @param string paramName		the name of the new parameter to add.
 * @param string paramValue		the value of the new parameter to add.
 */
VidiunClientBase.prototype.addParam = function(params, paramName, paramValue)
{
	if (paramValue == null)
		return;
	if(typeof(paramValue) != 'object') {
		params[paramName] = paramValue;
		return;
	}
	for(var subParamName in paramValue) {
		var subParamValue = paramValue[subParamName];
		this.addParam(params, paramName + ":" + subParamName, subParamValue);
	}
};

/**
 * set to true to indicate a multirequest is being defined.
 */
VidiunClientBase.prototype.startMultiRequest = function()
{
	this.useMultiRequest = true;
};

/**
 * execute a multirequest.
 */
VidiunClientBase.prototype.doMultiRequest = function(callback)
{
	return this.doQueue(callback);
};

/**
 * indicate if current mode is constructing a multirequest or single requests.
 */
VidiunClientBase.prototype.isMultiRequest = function()
{
	return this.useMultiRequest;	
};

/**
 * @param string msg	client logging utility. 
 */
VidiunClientBase.prototype.log = function(msg)
{
	if (this.shouldLog)
		this.config.getLogger().log(msg);
};

/**
 * Abstract base class for all client objects
 */
function VidiunObjectBase()
{
}

/**
 * Abstract base class for all client services
 * Initialize the service keeping reference to the VidiunClient
 * @param VidiunClientm client
 */
function VidiunServiceBase()
{
}
VidiunServiceBase.prototype.init = function(client)
{
	this.client = client;
};
/**
 * @param VidiunClient
 */
VidiunServiceBase.prototype.client = null;

/**
 * Constructs new Vidiun configuration object
 * @param partnerId		a valid Vidiun partner id.
 */
function VidiunConfiguration(partnerId)
{
	if(!partnerId)
		partnerId = -1;
    if (typeof(partnerId) != 'number')
        throw "Invalid partner id - partnerId must be numeric!";
    this.partnerId = partnerId;
}

VidiunConfiguration.prototype.logger		= null;
VidiunConfiguration.prototype.serviceUrl	= "http://www.vidiun.com";
VidiunConfiguration.prototype.serviceBase 	= "/api_v3/index.php?service=";
VidiunConfiguration.prototype.partnerId	= null;
VidiunConfiguration.prototype.format		= VidiunClientBase.prototype.VIDIUN_SERVICE_FORMAT_JSONP;
VidiunConfiguration.prototype.clientTag	= "js";

/**
 * Set logger to get vidiun client debug logs.
 * @param IVidiunLogger log
 */
VidiunConfiguration.prototype.setLogger = function(log)
{
	this.logger = log;
};

/**
 * Gets the logger (Internal client use)
 * @return IVidiunLogger
 */
VidiunConfiguration.prototype.getLogger = function()
{
	return this.logger;
};
