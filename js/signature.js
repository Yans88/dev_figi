/*var uagent = navigator.userAgent.toLowerCase();
var isTouchDevice = /(iphone|ipad)/i.test(uagent); // uagent.search(/(iphone|ipad)/) > -1;
var isCanvasEmpty = true;
//var points = new Array();
if (window.addEventListener) {
    window.addEventListener('load', function () {
        var canvas, context, tool, container;

        function init() {
            // Find the canvas element.
            canvas = document.getElementById('imageView');
            container = document.getElementById('container');
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
            //context.fillText('isTouchDevice: '+ isTouchDevice, 15, 15);
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
            /*
				dojo.connect(canvas, "touchstart", ev_canvas);
				dojo.connect(canvas, "touchmove", ev_canvas);
				dojo.connect(canvas, "touchend", ev_canvas);	
				
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
					isCanvasEmpty = false;
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
                isCanvasEmpty=false;
              };
            };
           this.touchend = function (ev) {
            if (tool.started) {
              tool.started = false;
            }
          };
           
        }
        // The general-purpose event handler. This function just determines the mouse
        // position relative to the canvas element.
        function ev_canvas(ev) {
            if (isTouchDevice && ev.touches) {
              if (ev.touches.length>0) {
                var p = $('#imageView').offset();
                context.fillText('touchmove'+p.left, p.left+10, 20);
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
            //points.push(ev);
        }

        init();

    }, false);
}

function ResetSignature() {
    var canvasReset = document.getElementById('imageView');
    var contextReset = canvasReset.getContext('2d');

    contextReset.fillStyle = '#000000';
    contextReset.fillRect(0, 0, $('#imageView').css('width'), $('#imageView').css('height'));
    canvasReset.width = canvasReset.width;
    canvasReset.width = canvasReset.width;

    //alert(points.length);
    //points = new Array();
    isCanvasEmpty = true;
}

function SaveImage() {
    var CanvasToSave = document.getElementById('imageView');

    var oImg = Canvas2Image.saveAsPNG(CanvasToSave, true);

    $('#ImageToSave').html(oImg);

    $('#SigCover').css('z-index', 102);
    $('#SigCover').css('left', 23);
    $('#SigCover').css('width', 402);
    $('#SigCover').css('height', 152);
    $('#SigCoverText').css('z-index', 101);
    $('#SigCoverText').css('left', 23);
    $('#SigCoverText').css('width', 400);
    $('#SigCoverText').css('height', 150);
    //alert(points.length);
}
*/
var isCanvasEmpty = true;
var wrapper = document.getElementById("signature-pad"),
    clearButton = wrapper.querySelector("[data-action=clear]"),
    canvas = wrapper.querySelector("canvas"),
    signaturePad;
	signaturePad = new SignaturePad(canvas,{
		onEnd: 	function(){
						isCanvasEmpty = false;
					}
	});
	clearButton.addEventListener("click", function (event) {
		signaturePad.clear();
	});