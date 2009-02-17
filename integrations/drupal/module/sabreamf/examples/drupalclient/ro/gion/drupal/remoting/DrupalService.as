/**
 * This code is distributed under WTFPL (seriously)
 * - so do whatever you want with it
 */

package ro.gion.drupal.remoting {

	import flash.events.*;
	import flash.net.NetConnection;
	import flash.net.ObjectEncoding;
	import flash.net.Responder;
	
	import flash.utils.*;
	
	public dynamic class DrupalService extends Proxy {
		
		private static const STATUS_DISCONNECTED = 'DrupalService.STATUS_DISCONNECTED';
		private static const STATUS_CONNECTING = 'DrupalService.STATUS_CONNECTING';
		private static const STATUS_CONNECTED = 'DrupalService.STATUS_CONNECTED';
		
		private var nc:NetConnection;
		private var gatewayUrl:String;
		private var serviceName:String;
		
		private var apiKey:String = null;
		private var sessionId:String = null;
		
		private var status:String = DrupalService.STATUS_DISCONNECTED;
		private var connectionObject:Object = null;
		private var user:Object = null;
		
		private var nextCalls:Array = new Array();
		
		public function DrupalService(gatewayUrl:String, serviceName:String, apiKey:String=null, sessionId:String=null) {
			
			this.gatewayUrl = gatewayUrl;
			this.serviceName = serviceName;
			this.apiKey = apiKey;
			this.sessionId = sessionId;
			
			nc = new NetConnection();
			nc.addEventListener(NetStatusEvent.NET_STATUS, netStatusEvent_Handler);
			nc.addEventListener(IOErrorEvent.IO_ERROR, ioErrorEvent_Handler);
			nc.addEventListener(SecurityErrorEvent.SECURITY_ERROR, securityErrorEvent_Handler);
			nc.objectEncoding = ObjectEncoding.AMF3;
			nc.connect(this.gatewayUrl);
		}
		
		public function systemConnect() {
			__trace('calling system.connect');
			status = STATUS_CONNECTING;
			var command:String = 'system.connect';
			var responder:Responder = new Responder(systemConnectResult_Handler, systemConnectFault_Handler);
			var args:Array = new Array(command, responder);
			if(apiKey != null) {
				args.push(apiKey);
			}
			nc.call.apply(nc, args);
		}
		
		private function systemConnectResult_Handler(result:Object):void {
			__trace('connected through : ' + gatewayUrl);
			__trace('received sessionId: ' + result.sessid);
			__trace('received userId   : ' + result.user.userid);
			
			sessionId = result.sessid;
			user = result.user;
			status = STATUS_CONNECTED;
			
			var cb:Object = null;		
			while(cb = nextCalls.shift()) {
				__trace('calling pending: ' + cb.name);
				cb.callback.apply(cb.name, cb.args);
			}
		}
		
		private function systemConnectFault_Handler(result:Object):void {
			__trace('systemConnectFault ' + result);
			status = STATUS_DISCONNECTED;
		}
		
		private function netStatusEvent_Handler(event:NetStatusEvent):void {
			__trace('netStatusEvent_Handler ' + event.info.code);
			switch(event.info.code) {
				case 'NetConnection.Call.Failed':
				break;
				case 'NetConnection.Connect.Success':
					__trace(event.info.code);
				break;
				default:
				break;
			}
		}
		
		private function __trace(el:*) {
			if((el is Number) || (el is String) || (el is Boolean) || (el is Date)) {
				trace(serviceName + ' - ' + el);
				return;
			}
			for(var i in el) {
				trace(serviceName + ' - ' + i + ':' + el);
				if(el[i] is Object) {
					__trace(el[i]);
				}
			}
		}

		
		private function genericResult_Handler(result:*):void {
			__trace('got unhandled result');
			__trace(result);
		}
		
		private function genericFault_Handler(fault:*):void {
			__trace('got unhandled fault');
			__trace(fault);
		}
		
		private function ioErrorEvent_Handler(event:IOErrorEvent):void {
			__trace(event);
		}
		
		private function securityErrorEvent_Handler(event:SecurityErrorEvent):void {
			__trace(event);
		}
		
		flash_proxy override function callProperty(method: *, ...args): * {
			
			var clazz : Class = getDefinitionByName(getQualifiedClassName(this)) as Class;
			clazz.prototype[method] = function() {
				arguments[1] = arguments[1] != null ? arguments[1] : genericResult_Handler;
				arguments[2] = arguments[2] != null ? arguments[2] : genericFault_Handler;
				var command:String = serviceName+'.'+serviceName+'_'+method;
				var responder:Responder = new Responder(arguments[1], arguments[2]);
				__trace(responder);
				arguments[0].unshift(command, responder);
				nc.call.apply(nc, arguments[0]);
				return true;
			}
			
			switch(status) {
				case STATUS_CONNECTED:
					return clazz.prototype[method].apply(method, args);
				break;
				case STATUS_CONNECTING:
					nextCalls.push({name: method, args: args, callback: clazz.prototype[method]});
				break;
				case STATUS_DISCONNECTED:
					systemConnect();
					nextCalls.push({name: method, args: args, callback: clazz.prototype[method]});
				break;
				default:break;
			}
		}
	}
	
}