This module provides a one frame fla file for testing service invocation.

There is an as3 wrapper class that helps you invoke in an easy manner, the drupal remote object methods(aka services).
It can be used with flex too (tested in flex3)

Code in the fla's first frame:

import flash.events.NetStatusEvent;
import flash.net.NetConnection;
import flash.net.ObjectEncoding;
import flash.net.Responder;

// you are welcomed to adapt this as3 proxy class(aka class with unknown/dynamic methods) as you feeld it is needed
import ro.gion.drupal.remoting.DrupalService;

// first argument, the gateway url, second argument, the service name(module name)
// the code bellow creates an in instance of a remote object(proxy to your drupal service)
// you can have as many instances as you want, each of them poiting to different or the same service
var gw:DrupalService = new DrupalService(
	'http://localhost/drupal/services/sabreamf',
	'sabreamf'
);

// reponder callbacks, these are invoked when result is received from the server
// communication between flash and the server side php/drupal is done in an async manner
// you have to deal with "transitorial"(bad english) states yourself
function sabreamf_pingme_Result(re:Object) {
	trace('sabreamf_pingme_Result result ' + re);
}

// responder callback when called when smth bad happened(server died, the internet connection died, etc.)
function sabreamf_pingme_Fault(fe:Object) {
	trace('sabreamf_pingme_Fault fault ' + fe);
}

// hooks are called without the module prefix (to ease your pain, in the DrupalService constructor above
// you already told the proxy what to use
// for the 4 calls bellow you should receive 4 results or faults
gw.pingme([21, 24], sabreamf_pingme_Result, sabreamf_pingme_Fault);
gw.pingme([22, 24], sabreamf_pingme_Result, sabreamf_pingme_Fault);
gw.pingme([23, 24], sabreamf_pingme_Result, sabreamf_pingme_Fault);
gw.pingme([24, 24], sabreamf_pingme_Result, sabreamf_pingme_Fault);