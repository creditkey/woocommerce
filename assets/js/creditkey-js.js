/*!
 * @credit-key/creditkey-js v1.3.1 - https://www.creditkey.com
 * MIT Licensed
 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ck"] = factory();
	else
		root["ck"] = factory();
})(window, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(process) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return api; });
/* unused harmony export applyUI */
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return marketingUI; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return pdpHost; });


var DEV = 'development';
var STAGE = 'staging';
var PREVIEW = 'preview';
var PROD = 'production';
var api = function api(platform) {
  if (platform === DEV) return process.env.REACT_APP_API ? process.env.REACT_APP_API : 'http://ck-web.creditkey.localhost';
  if (platform === PREVIEW) return 'https://preview.creditkey.com';
  if (platform === STAGE) return 'https://staging.creditkey.com/app';
  if (platform === PROD) return 'https://www.creditkey.com/app';
  return platform; // custom URL - for testing
};

var applyUI = function applyUI(platform) {
  if (platform === DEV) return process.env.REACT_APP_APPLY_UI ? process.env.REACT_APP_APPLY_UI : 'http://localhost:3001';
  if (platform === PREVIEW) return 'https://apply.preview.creditkey.com';
  if (platform === STAGE) return 'https://staging-apply.creditkey.com';
  if (platform === PROD) return 'https://apply.creditkey.com';
  return platform; // custom URL - for testing
};

var marketingUI = function marketingUI(platform) {
  if (platform === DEV) return process.env.REACT_APP_MARKETING_UI ? process.env.REACT_APP_MARKETING_UI : 'http://localhost:3002';
  if (platform === STAGE) return 'https://staging-marketing.creditkey.com';
  if (platform === PREVIEW) return 'https://marketing.preview.creditkey.com';
  if (platform === PROD) return 'https://marketing.creditkey.com';
  return platform; // custom URL - for testing
};

var pdpHost = function pdpHost(resource, platform) {
  var host = window.location.hostname;
  if (host.indexOf('staging') >= 0 || host.indexOf('dev') >= 0) {
    return resource(STAGE);
  }
  if (host.indexOf('preview') >= 0 || host.indexOf('dev') >= 0) {
    return resource(PREVIEW);
  }
  if (host.indexOf('localhost') >= 0) {
    return resource(DEV);
  }
  if (platform) {
    return resource(platform);
  }
  switch (host) {
    case 'creditkey.magento2':
      return resource(DEV);
      break;
    case 'katom.app':
    case 'packnwood-demo.wjserver960.com':
    case 'magento.creditkey.com':
    case 'demo.creditkey.com':
    case 'demo.creditkey.tech':
      return resource(STAGE);
      break;
    case 'magento2.creditkey.com':
      return resource(PROD);
      break;
    default:
      return resource(PROD);
  }
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(2)))

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(7);


/***/ }),
/* 2 */
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

var api = __webpack_require__(4);
            var content = __webpack_require__(5);

            content = content.__esModule ? content.default : content;

            if (typeof content === 'string') {
              content = [[module.i, content, '']];
            }

var options = {};

options.insert = "head";
options.singleton = false;

var update = api(content, options);



module.exports = content.locals || {};

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var isOldIE = function isOldIE() {
  var memo;
  return function memorize() {
    if (typeof memo === 'undefined') {
      // Test for IE <= 9 as proposed by Browserhacks
      // @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
      // Tests for existence of standard globals is to allow style-loader
      // to operate correctly into non-standard environments
      // @see https://github.com/webpack-contrib/style-loader/issues/177
      memo = Boolean(window && document && document.all && !window.atob);
    }

    return memo;
  };
}();

var getTarget = function getTarget() {
  var memo = {};
  return function memorize(target) {
    if (typeof memo[target] === 'undefined') {
      var styleTarget = document.querySelector(target); // Special case to return head of iframe instead of iframe itself

      if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
        try {
          // This will throw an exception if access to iframe is blocked
          // due to cross-origin restrictions
          styleTarget = styleTarget.contentDocument.head;
        } catch (e) {
          // istanbul ignore next
          styleTarget = null;
        }
      }

      memo[target] = styleTarget;
    }

    return memo[target];
  };
}();

var stylesInDom = [];

function getIndexByIdentifier(identifier) {
  var result = -1;

  for (var i = 0; i < stylesInDom.length; i++) {
    if (stylesInDom[i].identifier === identifier) {
      result = i;
      break;
    }
  }

  return result;
}

function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];

  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var index = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3]
    };

    if (index !== -1) {
      stylesInDom[index].references++;
      stylesInDom[index].updater(obj);
    } else {
      stylesInDom.push({
        identifier: identifier,
        updater: addStyle(obj, options),
        references: 1
      });
    }

    identifiers.push(identifier);
  }

  return identifiers;
}

function insertStyleElement(options) {
  var style = document.createElement('style');
  var attributes = options.attributes || {};

  if (typeof attributes.nonce === 'undefined') {
    var nonce =  true ? __webpack_require__.nc : undefined;

    if (nonce) {
      attributes.nonce = nonce;
    }
  }

  Object.keys(attributes).forEach(function (key) {
    style.setAttribute(key, attributes[key]);
  });

  if (typeof options.insert === 'function') {
    options.insert(style);
  } else {
    var target = getTarget(options.insert || 'head');

    if (!target) {
      throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
    }

    target.appendChild(style);
  }

  return style;
}

function removeStyleElement(style) {
  // istanbul ignore if
  if (style.parentNode === null) {
    return false;
  }

  style.parentNode.removeChild(style);
}
/* istanbul ignore next  */


var replaceText = function replaceText() {
  var textStore = [];
  return function replace(index, replacement) {
    textStore[index] = replacement;
    return textStore.filter(Boolean).join('\n');
  };
}();

function applyToSingletonTag(style, index, remove, obj) {
  var css = remove ? '' : obj.media ? "@media ".concat(obj.media, " {").concat(obj.css, "}") : obj.css; // For old IE

  /* istanbul ignore if  */

  if (style.styleSheet) {
    style.styleSheet.cssText = replaceText(index, css);
  } else {
    var cssNode = document.createTextNode(css);
    var childNodes = style.childNodes;

    if (childNodes[index]) {
      style.removeChild(childNodes[index]);
    }

    if (childNodes.length) {
      style.insertBefore(cssNode, childNodes[index]);
    } else {
      style.appendChild(cssNode);
    }
  }
}

function applyToTag(style, options, obj) {
  var css = obj.css;
  var media = obj.media;
  var sourceMap = obj.sourceMap;

  if (media) {
    style.setAttribute('media', media);
  } else {
    style.removeAttribute('media');
  }

  if (sourceMap && btoa) {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  } // For old IE

  /* istanbul ignore if  */


  if (style.styleSheet) {
    style.styleSheet.cssText = css;
  } else {
    while (style.firstChild) {
      style.removeChild(style.firstChild);
    }

    style.appendChild(document.createTextNode(css));
  }
}

var singleton = null;
var singletonCounter = 0;

function addStyle(obj, options) {
  var style;
  var update;
  var remove;

  if (options.singleton) {
    var styleIndex = singletonCounter++;
    style = singleton || (singleton = insertStyleElement(options));
    update = applyToSingletonTag.bind(null, style, styleIndex, false);
    remove = applyToSingletonTag.bind(null, style, styleIndex, true);
  } else {
    style = insertStyleElement(options);
    update = applyToTag.bind(null, style, options);

    remove = function remove() {
      removeStyleElement(style);
    };
  }

  update(obj);
  return function updateStyle(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap) {
        return;
      }

      update(obj = newObj);
    } else {
      remove();
    }
  };
}

module.exports = function (list, options) {
  options = options || {}; // Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
  // tags it will allow on a page

  if (!options.singleton && typeof options.singleton !== 'boolean') {
    options.singleton = isOldIE();
  }

  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];

    if (Object.prototype.toString.call(newList) !== '[object Array]') {
      return;
    }

    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDom[index].references--;
    }

    var newLastIdentifiers = modulesToDom(newList, options);

    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];

      var _index = getIndexByIdentifier(_identifier);

      if (stylesInDom[_index].references === 0) {
        stylesInDom[_index].updater();

        stylesInDom.splice(_index, 1);
      }
    }

    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

// Imports
var ___CSS_LOADER_API_IMPORT___ = __webpack_require__(6);
exports = ___CSS_LOADER_API_IMPORT___(false);
// Module
exports.push([module.i, "/* CreditKey Styles - Converted from sass for compatibility */\n.creditkey {\n  all: initial;\n}\n\n.creditkey * {\n  all: unset;\n}\n\n.creditkey {\n  z-index: 50000;\n  text-decoration: none !important;\n  font-family: \"Proxima Nova\", \"Helvetica Neue\", Helvetica, Arial, sans-serif;\n}\n\n.creditkey a {\n  text-decoration: none !important;\n}\n\n/* Modal styles based on bulma - properly namespaced under .creditkey */\n.creditkey .button {\n  background-color: #3D9CE5 !important;\n  min-height: 40px !important;\n  border-width: 0 !important;\n  vertical-align: middle !important;\n  text-decoration: none !important;\n}\n\n.creditkey .ck-modal {\n  margin: 0 !important;\n  padding-top: 50px;\n  max-width: 100% !important;\n  width: 100% !important;\n  visibility: visible !important;\n  background: transparent !important;\n  position: absolute;\n  justify-content: normal;\n  align-items: flex-start;\n  display: flex;\n  overflow: hidden;\n  z-index: 50001;\n  bottom: 0;\n  left: 0;\n  right: 0;\n  top: 0;\n}\n\n@media screen and (max-device-width: 480px) {\n  .creditkey .ck-modal {\n    padding-top: 0px !important;\n  }\n}\n\n.creditkey .ck-modal-background {\n  background-color: rgba(10, 10, 10, 0.86);\n  bottom: 0;\n  left: 0;\n  position: fixed;\n  right: 0;\n  top: 0;\n}\n\n.creditkey .ck-modal-content,\n.creditkey .ck-modal-card {\n  min-height: -webkit-min-content !important;\n  min-height: min-content !important;\n  max-height: none;\n  height: auto !important;\n  margin: 0 20px;\n  max-height: calc(100vh - 160px);\n  overflow: auto;\n  position: relative;\n  width: 100%;\n}\n\n@media screen and (min-width: 769px) {\n  .creditkey .ck-modal-content,\n  .creditkey .ck-modal-card {\n    min-height: -webkit-min-content !important;\n    min-height: min-content !important;\n    max-height: none;\n    height: auto !important;\n    margin: 0 auto;\n    max-height: calc(100vh - 40px);\n    width: 650px;\n  }\n}\n\n.creditkey .ck-modal-card {\n  min-height: -webkit-min-content !important;\n  min-height: min-content !important;\n  max-height: none !important;\n}\n\n.creditkey .ck-modal-content {\n  overflow: hidden;\n  -webkit-overflow-scrolling: touch;\n  border-radius: 5px;\n  background-color: #fff;\n  background-image: url(\"https://www.creditkey.com/app/assets/header/ck-nav-logo-d79f74bc03213d02a5ab4cd1c484cfcfb533c2abf5f05ee35cd67c5239693a28.svg\");\n  background-repeat: no-repeat;\n  background-position: center;\n  height: auto;\n  min-height: -webkit-min-content;\n  min-height: min-content;\n  max-height: none;\n}\n\n@media screen and (max-width: 768px) {\n  .creditkey .ck-modal-content {\n    height: 100%;\n    border-radius: 0 !important;\n  }\n}\n\n/* Custom classes - properly namespaced under .creditkey */\n.creditkey #creditkey-iframe {\n  margin: auto;\n  width: 100%;\n  border: none;\n  height: inherit;\n}\n\n.creditkey .payment-icon {\n  display: inline-block !important;\n  margin-right: 5px !important;\n  vertical-align: middle !important;\n}\n\n.creditkey .terms {\n  text-decoration: underline;\n  color: #3D9CE5;\n  cursor: pointer;\n}\n\n.creditkey .terms:hover {\n  text-decoration: none;\n}\n\n.creditkey .pdp {\n  padding: 0 5px 0 0;\n  font-size: 16px !important;\n  font-weight: bold;\n}\n\n.creditkey .pdp-text {\n  font-size: 16px !important;\n  font-weight: 400;\n}\n\n.creditkey .ck-offer {\n  float: right;\n  text-align: left;\n}\n\n.creditkey .ck-logo-small {\n  height: 22px !important;\n}\n\n.creditkey .ck-logo-medium {\n  height: 22px !important;\n}\n\n.creditkey .ck-logo-large {\n  height: 22px !important;\n}\n\n/* This selector remains at root level as it was in the original sass */\n#creditkey-pdp-iframe {\n  width: 100% !important;\n  max-height: 70px !important;\n}\n", ""]);
// Exports
module.exports = exports;


/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
// eslint-disable-next-line func-names
module.exports = function (useSourceMap) {
  var list = []; // return the list of modules as css string

  list.toString = function toString() {
    return this.map(function (item) {
      var content = cssWithMappingToString(item, useSourceMap);

      if (item[2]) {
        return "@media ".concat(item[2], " {").concat(content, "}");
      }

      return content;
    }).join('');
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === 'string') {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, '']];
    }

    var alreadyImportedModules = {};

    if (dedupe) {
      for (var i = 0; i < this.length; i++) {
        // eslint-disable-next-line prefer-destructuring
        var id = this[i][0];

        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }

    for (var _i = 0; _i < modules.length; _i++) {
      var item = [].concat(modules[_i]);

      if (dedupe && alreadyImportedModules[item[0]]) {
        // eslint-disable-next-line no-continue
        continue;
      }

      if (mediaQuery) {
        if (!item[2]) {
          item[2] = mediaQuery;
        } else {
          item[2] = "".concat(mediaQuery, " and ").concat(item[2]);
        }
      }

      list.push(item);
    }
  };

  return list;
};

function cssWithMappingToString(item, useSourceMap) {
  var content = item[1] || ''; // eslint-disable-next-line prefer-destructuring

  var cssMapping = item[3];

  if (!cssMapping) {
    return content;
  }

  if (useSourceMap && typeof btoa === 'function') {
    var sourceMapping = toComment(cssMapping);
    var sourceURLs = cssMapping.sources.map(function (source) {
      return "/*# sourceURL=".concat(cssMapping.sourceRoot || '').concat(source, " */");
    });
    return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
  }

  return [content].join('\n');
} // Adapted from convert-source-map (MIT)


function toComment(sourceMap) {
  // eslint-disable-next-line no-undef
  var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
  var data = "sourceMappingURL=data:application/json;charset=utf-8;base64,".concat(base64);
  return "/*# ".concat(data, " */");
}

/***/ }),
/* 7 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./src/utils/request.js
/**
 * @private
 * @function request
 * @description Make a request to the server and return a promise.
 * @param {string} url
 * @param {object} options
 * @returns {promise}
 */
function handleErrors(response) {
  if (!response.ok) {
    throw Error(response.statusText);
  }
  return response;
}
function request(url, options) {
  return new Promise(function (resolve, reject) {
    if (!url) reject(new Error('URL parameter required'));
    if (!options) reject(new Error('Options parameter required'));
    fetch(url, options).then(function (response) {
      return handleErrors(response);
    }).then(function (response) {
      return response.json();
    }).then(function (response) {
      if (response.errors) reject(response.errors);else resolve(response);
    })["catch"](function (err) {
      return reject(err);
    });
  });
}
// EXTERNAL MODULE: ./src/utils/platform.js
var utils_platform = __webpack_require__(0);

// CONCATENATED MODULE: ./src/utils/network.js
function _extends() { _extends = Object.assign ? Object.assign.bind() : function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }



/**
 * @function Network
 * @description Factory function to create a object that can send
 * requests to a specific resource on the server.
 * @param {string} resource The resource used for config
 */
var network_Network = function Network(platform, resource) {
  if (!platform) return false;
  var buildURL = function buildURL(id, resource) {
    var parameters = [Object(utils_platform["a" /* api */])(platform)];
    if (resource) parameters = parameters.concat([resource]);
    if (id) parameters = parameters.concat([id]);
    return parameters.join('/');
  };

  // Default options used for every request
  var defaultOptions = {
    mode: 'cors',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  };
  return {
    /**
     * @function post
     * @description Make a POST request.
     * @param {string} path
     * @param {object} body
     * @param {object} options
     * @returns {promise}
     */
    post: function post(path, body, options) {
      if (options === void 0) {
        options = {};
      }
      return request(buildURL(path), _extends({}, options, defaultOptions, {
        method: 'POST',
        body: JSON.stringify(body)
      }));
    },
    /**
     * @function post
     * @description Make a GET request.
     * @param {string} path
     * @param {object} options
     * @returns {promise}
     */
    get: function get(path, options) {
      if (options === void 0) {
        options = {};
      }
      return request(buildURL(path), _extends({}, options, defaultOptions, {
        method: 'GET'
      }));
    },
    /**
     * @function edit
     * @description Make a PUT request.
     * @param {string} path
     * @param {object} body
     * @param {object} options
     * @returns {promise}
     */
    edit: function edit(path, body, options) {
      if (options === void 0) {
        options = {};
      }
      return request(buildURL(path), _extends({}, options, defaultOptions, {
        method: 'PUT',
        body: JSON.stringify(body)
      }));
    },
    /**
     * @function delete
     * @description Make a DELETE request.
     * @param {string} path
     * @param {object} options
     * @returns {promise}
     */
    "delete": function _delete(path, options) {
      if (options === void 0) {
        options = {};
      }
      return request(buildURL(path), _extends({}, options, defaultOptions, {
        method: 'DELETE'
      }));
    },
    ping: function ping() {
      return request(buildURL(), {
        method: 'GET'
      });
    }
  };
};
/* harmony default export */ var network = (network_Network);
// EXTERNAL MODULE: ./src/styles/index.css
var styles = __webpack_require__(3);

// CONCATENATED MODULE: ./src/lib/components/modal.js


var _modal = function modal(source, completionCallback) {
  registerPostMessageCallback(completionCallback);

  // Check to see if we've already created the modal - but hidden it when the user clicked off.
  // If so, simply redisplay the modal.
  var existingModal = document.getElementById('creditkey-modal');
  var sourceUrl = new URL(source);
  sourceUrl.searchParams.append('modal', true);
  if (existingModal !== null) {
    var iframe = document.getElementById('creditkey-iframe');
    var url = iframe.src;
    if (url !== "" + sourceUrl.href) {
      existingModal.remove();
      return _modal(source);
    }
    existingModal.style.display = 'flex';
    // Remove old event listeners before re-adding when showing existing modal
    removeModalEventListeners();
    addModalEventListeners();
  } else {
    // Otherwise, create the modal.

    var body = document.body;
    // default height set for UX during load, will be changed via updateParent() from inside iframe content later
    var _iframe = "<iframe scrolling=\"no\" id=\"creditkey-iframe\" src=\"" + sourceUrl.href + "\" style=\"height: 100vh;\"></iframe>";
    if (!validate_url(source)) {
      _iframe = "An invalid resource was requested";
    }
    body.insertAdjacentHTML('beforeend', "<div class=\"creditkey\" id=\"creditkey-modal\"><div class=\"ck-modal is-active\"><div class=\"ck-modal-background\"></div><div class=\"ck-modal-content\" id=\"ck-modal-card\">" + _iframe + "</div></div></div>");

    // Add event listeners for ESC key and background click
    addModalEventListeners();
  }
};
function remove() {
  // Hide the modal so we can potentially redisplay it, leaving the user at the same place in the
  // checkout flow, if they accidentially click off.
  var el = document.getElementById('creditkey-modal');
  if (el !== null) {
    el.style.display = 'none';
    // Remove event listeners when hiding modal
    removeModalEventListeners();
  }
}

// ensure that we're requesting a valid creditkey domain
function validate_url(url) {
  if (!url) return false;
  var root = url.split('/')[1];
  if (Object(utils_platform["a" /* api */])('development').split('/')[1] === root) return true;
  if (Object(utils_platform["a" /* api */])('staging').split('/')[1] === root) return true;
  if (Object(utils_platform["a" /* api */])('production').split('/')[1] === root) return true;
  return false;
}
function redirect(uri) {
  if (navigator.userAgent.match(/Android/i)) {
    document.location = uri;
  } else {
    window.location.replace(uri);
  }
}
function registerPostMessageCallback(completionCallback) {
  window.addEventListener('message', function (e) {
    if (!e) return false;
    if (e && !e.data) return false;
    var event;
    try {
      event = JSON.parse(e.data);
    } catch (e) {
      event = false;
    }
    if (!event || !event.action) return false;
    var modal_element = document.getElementById('ck-modal-card');
    var iframe_element = document.getElementById('creditkey-iframe');
    if (!iframe_element || !modal_element) return false;

    // if we're closing the modal from within the CK iframe, trigger the event bound to parent body
    if (event.action === 'cancel' && event.type === 'modal') {
      remove();
    } else if (event.action == 'complete' && event.type == 'modal') {
      if (completionCallback) {
        var params = new URL(event.options);
        completionCallback(params.searchParams.get('id'), remove);
      } else {
        redirect(event.options);
      }
    } else if (event.action == 'height' && event.type == 'modal') {
      var total_height = event.options + 14; // 14 allows padding underneath content (usually legal footer)

      // set the iframe, the parent div, and that div's parent height to something that adjusts to content height
      iframe_element.style.height = total_height.toString() + 'px';

      // Pad parent div height because issues where Chrome's calc'd <body> height is different than other browsers
      //  which cuts of the bottom rounded corners
      if (total_height + 60 > window.innerHeight) {
        modal_element.parentNode.style.height = (total_height + 60).toString() + 'px';
      }

      // force scroll to top because modal starts at top of page.
      window.scrollTo({
        top: 0,
        left: 0,
        behavior: 'smooth'
      });
    }
  }, false);
}

// Event handlers for ESC key and background click
var escKeyHandler;
var backgroundClickHandler;
function addModalEventListeners() {
  // ESC key handler
  escKeyHandler = function escKeyHandler(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      var modal = document.getElementById('creditkey-modal');
      if (modal && modal.style.display !== 'none') {
        remove();
      }
    }
  };

  // Background click handler
  backgroundClickHandler = function backgroundClickHandler(e) {
    var modal = document.getElementById('creditkey-modal');
    if (modal && modal.style.display !== 'none') {
      // Check if click target is the modal background (not the content)
      if (e.target.classList.contains('ck-modal-background')) {
        remove();
      }
    }
  };

  // Add event listeners
  document.addEventListener('keydown', escKeyHandler);
  document.addEventListener('click', backgroundClickHandler);
}
function removeModalEventListeners() {
  if (escKeyHandler) {
    document.removeEventListener('keydown', escKeyHandler);
    escKeyHandler = null;
  }
  if (backgroundClickHandler) {
    document.removeEventListener('click', backgroundClickHandler);
    backgroundClickHandler = null;
  }
}
/* harmony default export */ var modal = (_modal);
// CONCATENATED MODULE: ./src/lib/charges.js
var Charges = /*#__PURE__*/function () {
  function Charges(total, shipping, tax, discount_amount, grand_total) {
    this.data = {
      total: total,
      shipping: shipping,
      tax: tax,
      discount_amount: discount_amount,
      grand_total: grand_total
    };
  }
  var _proto = Charges.prototype;
  _proto.validate_charges = function validate_charges() {
    if (this.data.shipping && !this.is_valid_money_value(this.data.shipping)) return false;
    if (this.data.tax && !this.is_valid_money_value(this.data.tax)) return false;
    if (this.data.discount_amount && !this.is_valid_money_value(this.data.discount_amount)) return false;
    if (!this.is_valid_money_value(this.data.total) || !this.is_valid_money_value(this.data.grand_total)) {
      return false;
    }
    return true;
  };
  _proto.is_valid_money_value = function is_valid_money_value(value) {
    var num = +value;
    if (isNaN(num)) return false;
    return true;
  };
  return Charges;
}();

// CONCATENATED MODULE: ./src/lib/components/iframes.js



var iframes_frame = function frame(url, pointer) {
  if (pointer === void 0) {
    pointer = true;
  }
  iframes_registerPostMessageCallback();
  var style = '';
  if (!pointer) style = 'pointer-events: none;';
  var iframe = "<div className=\"iframe-container\"><iframe allowtransparency=\"true\" scrolling=\"no\" id=\"creditkey-pdp-iframe\" frameBorder=\"0\" style=\"" + style + "\" src=\"" + url + "\"></iframe></div>";
  return iframe;
};
function iframes_registerPostMessageCallback() {
  window.addEventListener('message', function (e) {
    var data;
    if (!e || !e.data) return false;
    try {
      data = JSON.parse(e.data);
    } catch (e) {
      return false;
    }
    if (data.action === 'pdp' && data.options.public_key) {
      var charges = new Charges(data.options.charges ? data.options.charges : '0, 0, 0, 0, 0'.split(','));
      var c = new client_Client(data.options.public_key, data.options.platform);
      c.enhanced_pdp_modal(charges);
    } else if (data.action === 'apply' && data.options.public_key) {
      modal(data.options.url);
    }
  });
}
// CONCATENATED MODULE: ./src/lib/client.js
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return typeof key === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (typeof input !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (typeof res !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }




var client_Client = /*#__PURE__*/function () {
  function Client(key, platform) {
    if (platform === void 0) {
      platform = 'development';
    }
    this.key = key;
    this.platform = platform;
    this.network = network(platform);
  }
  var _proto = Client.prototype;
  _proto.begin_checkout = function begin_checkout(cartItems, billingAddress, shippingAddress, charges, remoteId, customerId, returnUrl, cancelUrl, orderCompleteUrl, mode, merchant_data) {
    var _this = this;
    return new Promise(function (resolve, reject) {
      if (!cartItems || !billingAddress || !charges || !customerId || !returnUrl || !cancelUrl) {
        return reject('missing required data');
      }
      if (!Array.isArray(cartItems)) {
        return reject('cart items must be an array of CartItem objects');
      } else if (cartItems.filter(function (c) {
        return !c.is_valid_item();
      }).length >= 1) {
        return reject('one or more cart items are invalid');
      }
      if (typeof billingAddress !== 'object') {
        return reject('billing address should be an Address object');
      }
      if (typeof charges !== 'object') {
        return reject('charges should be a Charges object');
      } else if (!charges.validate_charges()) {
        return reject('charges value is invalid');
      }
      return _this.network.post('ecomm/begin_checkout' + _this.key_param, {
        cart_items: cartItems.map(function (item) {
          return item.data;
        }),
        shipping_address: shippingAddress && shippingAddress.data,
        billing_address: billingAddress.data,
        charges: charges.data,
        remote_id: remoteId,
        remote_customer_id: customerId,
        return_url: returnUrl,
        cancel_url: cancelUrl,
        order_complete_url: orderCompleteUrl,
        mode: mode || 'modal',
        merchant_data: merchant_data
      }).then(function (res) {
        return resolve(res);
      })["catch"](function (err) {
        return reject(err);
      });
    });
  };
  _proto.is_displayed_in_checkout = function is_displayed_in_checkout(cartItems) {
    var _this2 = this;
    return new Promise(function (resolve, reject) {
      if (!Array.isArray(cartItems)) {
        return reject('cart items must be an array of CartItem objects');
      } else if (cartItems.filter(function (c) {
        return !c.is_valid_item();
      }).length >= 1) {
        return reject('one or more cart items are invalid');
      }
      return _this2.network.post('ecomm/is_displayed_in_checkout' + _this2.key_param, {
        cart_items: cartItems.map(function (item) {
          return item.data;
        })
      }).then(function (res) {
        return res['is_displayed_in_checkout'] ? resolve(true) : reject(false);
      })["catch"](function (err) {
        return reject(err);
      });
    });
  }

  // display options are button, text, button_text
  // size options are small, medium, large, special (special loads a special version of the plain logo, instead of a sized badge version)
  // extra options can be:
  // 'special' = renders a special text only version of the pdp
  // 'static' = renders an unlinked version pf the pdp, basically a dumb banner
  // extra is ignored when 'none' or called with type checkout
  ;
  _proto.get_marketing_display = function get_marketing_display(charges, type, display, size, extra) {
    var _this3 = this;
    if (type === void 0) {
      type = "checkout";
    }
    if (display === void 0) {
      display = "button";
    }
    if (size === void 0) {
      size = "medium";
    }
    if (extra === void 0) {
      extra = "none";
    }
    return new Promise(function (resolve, reject) {
      return resolve(_this3.get_checkout_display(charges));
    });
  };
  _proto.enhanced_pdp_modal = function enhanced_pdp_modal(charges, type) {
    if (type === void 0) {
      type = 'pdp';
    }
    if (charges && typeof charges !== 'object') {
      return reject('charges should be a charges object');
    }
    var allowedTypes = ['pdp', 'cart'];
    if (!allowedTypes.includes(type)) return reject('invalid type, allowed types are "pdp", "cart"');
    var url = Object(utils_platform["c" /* pdpHost */])(utils_platform["b" /* marketingUI */], this.platform) + '/pdp/' + this.key + '/' + type + '/' + [charges.data.total, charges.data.shipping, charges.data.tax, charges.data.discount_amount, charges.data.grand_total].join(',');
    return modal(url);
  };
  _proto.get_apply_now = function get_apply_now(type, charges) {
    var url = Object(utils_platform["c" /* pdpHost */])(utils_platform["b" /* marketingUI */], this.platform) + '/apply.html?public_key=' + this.key + '&type=' + type + '&charges=' + [charges.data.total, charges.data.shipping, charges.data.tax, charges.data.discount_amount, charges.data.grand_total].join(',');
    return iframes_frame(url);
  };
  _proto.get_checkout_display = function get_checkout_display(charges) {
    if (charges && typeof charges !== 'object') {
      return reject('charges should be a charges object');
    }
    var url = Object(utils_platform["c" /* pdpHost */])(utils_platform["b" /* marketingUI */], this.platform) + '/checkout.html?public_key=' + this.key + '&charges=' + [charges.data.total, charges.data.shipping, charges.data.tax, charges.data.discount_amount, charges.data.grand_total].join(',');
    return iframes_frame(url, false);
  }

  // charges is a charges object
  ;
  _proto.get_pdp_display = function get_pdp_display(charges) {
    var view = 'pdp';
    var url = Object(utils_platform["c" /* pdpHost */])(utils_platform["b" /* marketingUI */], this.platform) + '/' + view + '.html?public_key=' + this.key + '&charges=' + [charges.data.total, charges.data.shipping, charges.data.tax, charges.data.discount_amount, charges.data.grand_total].join(',');
    return iframes_frame(url);
  };
  _proto.get_cart_display = function get_cart_display(charges, desktop, mobile) {
    var view = 'cart';
    if (desktop === void 0) {
      desktop = "right";
    }
    if (mobile === void 0) {
      mobile = "left";
    }
    var url = Object(utils_platform["c" /* pdpHost */])(utils_platform["b" /* marketingUI */], this.platform) + '/' + view + '.html?public_key=' + this.key + '&desktop=' + desktop + '&mobile=' + mobile + '&charges=' + [charges.data.total, charges.data.shipping, charges.data.tax, charges.data.discount_amount, charges.data.grand_total].join(',');
    return iframes_frame(url);
  };
  _proto.get_customer = function get_customer(email, customer_id) {
    if (!email || !customer_id) {
      return Promise.reject('Missing required paramters');
    }
    if (!/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)\b/.test(email)) {
      return Promise.reject('Invalid email address');
    }
    return this.network.post('ecomm/customer' + this.key_param, {
      email: email,
      customer_id: customer_id
    });
  };
  _createClass(Client, [{
    key: "key_param",
    get: function get() {
      return '?public_key=' + this.key;
    }
  }]);
  return Client;
}();

// CONCATENATED MODULE: ./src/lib/cart-item.js
var CartItem = /*#__PURE__*/function () {
  function CartItem(merchantProductId, name, price, sku, quantity, size, color) {
    this.data = {
      merchant_id: merchantProductId,
      name: name,
      price: price,
      sku: sku,
      quantity: quantity,
      size: size,
      color: color
    };
  }
  var _proto = CartItem.prototype;
  _proto.is_valid_item = function is_valid_item() {
    return !!(this.data.merchant_id && this.data.name);
  };
  return CartItem;
}();

// CONCATENATED MODULE: ./src/lib/address.js
var Address = /*#__PURE__*/function () {
  function Address(first_name, last_name, company_name, email, address1, address2, city, state, zip, phone_number) {
    this.data = {
      first_name: first_name,
      last_name: last_name,
      company_name: company_name,
      email: email,
      address1: address1,
      address2: address2 || '',
      city: city,
      state: state,
      zip: zip,
      phone_number: phone_number || ''
    };
  }
  var _proto = Address.prototype;
  _proto.is_valid_address = function is_valid_address() {
    for (var p in this.data) {
      if ((!this.data[p] || this.data[p] === '') && p !== 'address2') {
        return false;
      }
    }
    return true;
  };
  return Address;
}();

// CONCATENATED MODULE: ./src/lib/redirect.js
var redirect_redirect = function redirect(source) {
  var uri;
  var isModal = source.indexOf('modal');
  isModal >= 0 ? uri = source.replace('modal', 'redirect') : uri = source;
  if (navigator.userAgent.match(/Android/i)) document.location = uri;else window.location.href = uri;
};
/* harmony default export */ var lib_redirect = (redirect_redirect);
// CONCATENATED MODULE: ./src/lib/checkout.js


var checkout_checkout = function checkout(source, type, completionCallback) {
  if (type === void 0) {
    type = 'modal';
  }
  var width = window.screen.availWidth;
  if (type.toLowerCase() === 'modal' && width > 480) {
    return modal(source, completionCallback);
  } else {
    return lib_redirect(source);
  }
};
/* harmony default export */ var lib_checkout = (checkout_checkout);
// CONCATENATED MODULE: ./src/lib/apply.js



var apply_apply = function apply(key, type, platform) {
  if (type === void 0) {
    type = 'modal';
  }
  if (platform === void 0) {
    platform = 'production';
  }
  if (!key) {
    throw new Error('API public key required.');
  }
  window.scrollTo({
    top: 0,
    left: 0,
    behavior: 'smooth'
  });
  if (type.toLowerCase() === 'modal') {
    return modal(Object(utils_platform["a" /* api */])(platform) + '/apply/modal/start/' + key);
  } else if (type.toLowerCase() === 'redirect') {
    return lib_redirect(Object(utils_platform["a" /* api */])(platform) + '/apply/start/' + key);
  }
};
/* harmony default export */ var lib_apply = (apply_apply);
// CONCATENATED MODULE: ./src/index.js






/* harmony default export */ var src = __webpack_exports__["default"] = ({
  Client: client_Client,
  CartItem: CartItem,
  Address: Address,
  Charges: Charges,
  apply: lib_apply,
  checkout: lib_checkout
});

/***/ })
/******/ ])["default"];
});