/*var uagent = navigator.userAgent.toLowerCase();
var isTouchDevice = /(iphone|ipad)/i.test(uagent); // uagent.search(/(iphone|ipad)/) > -1;
var isCanvas2Empty = true;
//var points2 = new Array();
if (window.addEventListener) {
    window.addEventListener('load', function () {
        var canvas, context, tool, container;

        function init() {
            // Find the canvas element.
            canvas = document.getElementById('imageView2');
            //container = document.getElementById('container2');
            if (!canvas) {
                alert('Error: I cannot find the canvas element!');
                return;
            }

            if (!canvas.getContext) {
                alert('Error: no canvas.getContext!');
                return;
            }

            // Size the canvas:
            //canvas.width = 200;
            //canvas.height= 70;
            
            // Get the 2D canvas context.
            context = canvas.getContext('2d');
            if (!context) {
                alert('Error: failed to getContext!');
                return;
            }

            // Pencil tool instance.
            tool = new tool_pencil();

            // Attach the mousedown, mousemove and mouseup event listeners.
            if (isTouchDevice){
              canvas.addEventListener('touchstart', ev_canvas, false);
              canvas.addEventListener('touchmove', ev_canvas, false);
              canvas.addEventListener('touchend', ev_canvas, false);
             } else {
              canvas.addEventListener('mousedown', ev_canvas, false);
              canvas.addEventListener('mousemove', ev_canvas, false);
              canvas.addEventListener('mouseup', ev_canvas, false);
            }
            
        }

        // This painting tool works like a drawing pencil which tracks the mouse
        // movements.
        function tool_pencil() {
            var tool = this;
            this.started = false;

            // This is called when you start holding down the mouse button.
            // This starts the pencil drawing.
            this.mousedown = function (ev) {
                context.beginPath();
                context.moveTo(ev._x, ev._y);
                tool.started = true;
            };

            // This function is called every time you move the mouse. Obviously, it only
            // draws if the tool.started state is set to true (when you are holding down
            // the mouse button).
            this.mousemove = function (ev) {
                if (tool.started) {
                    context.lineTo(ev._x, ev._y);
                    context.stroke();
					isCanvas2Empty = false;
                }
            };

            // This is called when you release the mouse button.
            this.mouseup = function (ev) {
                if (tool.started) {
                    tool.mousemove(ev);
                    tool.started = false;
                }
            };
            	// touchstart
            this.touchstart = function (ev) {
              if (isTouchDevice)
                ev.preventDefault();
              //context.strokeStyle = '#f00';
              context.beginPath();
              context.moveTo(ev._x, ev._y);
              tool.started = true;

            };
            // touchmove
            this.touchmove = function (ev) {
               if (tool.started) {
                if (isTouchDevice)
                  ev.preventDefault();
                context.lineTo(ev._x, ev._y);
                context.stroke();		
                isCanvas2Empty=false;
               }
              };
           this.touchend = function (ev) {
            if (tool.started) {
              tool.started = false;
              //context.fillText('TEE', 10, 20);
            }
          };            
        }
        // The general-purpose event handler. This function just determines the mouse
        // position relative to the canvas element.
        function ev_canvas(ev) {
            if (isTouchDevice && ev.touches) {
              if (ev.touches.length>0) {
                var p = $('#imageView2').offset();
                ev._x = ev.targetTouches[0].pageX-p.left;
                ev._y = ev.targetTouches[0].pageY-p.top;
              }
            } else
            if (navigator.appName == 'Microsoft Internet Explorer' || navigator.vendor == 'Google Inc.' || navigator.vendor == 'Apple Computer, Inc.') { // IE or Chrome
                ev._x = ev.offsetX;
                ev._y = ev.offsetY;
            } else if (ev.layerX || ev.layerX == 0) { // Firefox
                ev._x = ev.layerX - this.offsetLeft;
                ev._y = ev.layerY - this.offsetTop;
            } else if (ev.offsetX || ev.offsetX == 0) { // Opera
                ev._x = ev.offsetX;
                ev._y = ev.offsetY;
            }
            // Call the event handler of the tool.
            var func = tool[ev.type];
            if (func) {
                func(ev);
            }
            //points2.push(ev);
        }

        init();

    }, false);
}

function ResetSignature2() {
    var canvasReset = document.getElementById('imageView2');
    var contextReset = canvasReset.getContext('2d');

    contextReset.fillStyle = '#000000';
    contextReset.fillRect(0, 0, $('imageView2').css('width'), $('imageView2').css('height'));
    canvasReset.width = canvasReset.width;
    canvasReset.width = canvasReset.width;

    //alert(points.length);
    //points2 = new Array();
    isCanvas2Empty = true;
}
*/

var isCanvas2Empty = true;
	
	var wrapper3 = document.getElementById("signature-pad3"),
    clearButton3 = wrapper3.querySelector("[data-action=clear]"),
    canvas3 = wrapper3.querySelector("canvas"),
    signaturePad3;
	signaturePad3 = new SignaturePad(canvas3,{
		onEnd: 	function(){
						isCanvas2Empty = false;
					}
	});
	clearButton3.addEventListener("click", function (event) {
		signaturePad3.clear();
	});