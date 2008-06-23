/*
 * The "bind()" function extension from Prototype.js, extracted for general use
 * 
 * @author Richard Harrison, http://www.pluggable.co.uk
 * @author Sam Stephenson (Modified from Prototype Javascript framework)
 * @license MIT-style license @see http://www.prototypejs.org/
 */
Function.prototype.bind = function(){
    // http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Functions:arguments
    var _$A = function(a){return Array.prototype.slice.call(a);}
    if(arguments.length < 2 && (typeof arguments[0] == "undefined")) return this;
    var __method = this, args = _$A(arguments), object = args.shift();
    return function() {
        return __method.apply(object, args.concat(_$A(arguments)));
    }
}

// End of core monkeying, which I know very well is frowned upon.

/*
Dase = function() {
    return {}
}();
*/

Dase.prefetch = function() {
    return {}
}();

Dase.prefetch.img = function(imgurl) {
    var img = new Image(100, 100);
    img.src = imgurl;
}

Dase.slideshow = function(name, container) {
    this.slides = [];
    //todo: break this up/clean up.
    this.name = name ? name : 'slideshow';
    //set up viewing pane/container
    if(!container) { this.container = document.createElement('div'); document.body.appendChild(this.container); }
    else this.container = container;
    $(this.container).addClass(this.CONTAINER_CLASS);
    this.image = document.createElement('img');
    $(this.image).addClass('dase_slideshow_slide');
    $(this.container).append(this.image);
    $(this.image).draggable().bind('mousedown', function() { this.style.cursor = 'move'; }).bind('mouseup', function() { this.style.cursor = 'pointer'; });
    $(window).bind('resize', this.autosize.bind(this));
    //set up controls (there may be more than one set...for now, we'll do this.)
    this.controls = document.createElement('div');
    $(this.controls).addClass(this.CONTROLS_CLASS);
    $(document.body).append(this.controls);
    this.controls.next_button = document.createElement('img');
    this.controls.prev_button = document.createElement('img');
    this.controls.zoomin_button = document.createElement('img');
    this.controls.zoomout_button = document.createElement('img');
    this.controls.status = document.createElement('span');
    $(this.controls).append(this.controls.prev_button);
    $(this.controls).append(this.controls.next_button);
    $(this.controls).append(document.createElement('br'));
    $(this.controls).append(this.controls.zoomout_button);
    $(this.controls).append(this.controls.zoomin_button);
    $(this.controls).append(document.createElement('br')).append(this.controls.status);
    $(this.controls.next_button).bind('click', (function() { this.advance(); }).bind(this)).addClass('next_btn').attr('src', 'images/nextbtn.png');
    $(this.controls.prev_button).bind('click', (function() { this.retreat(); }).bind(this)).addClass('prev_btn').attr('src', 'images/backbtn.png');
    $(window).bind('keypress', (function(e) { 
        if(e.which == 91) { this.retreat(); }; //'['
        if(e.which == 93) { this.advance(); }; //']'
        if(e.which == 45) { this.zoomOut(); }; //'-'
        if(e.which == 43) { this.zoomIn(); }; //'+'
        if(e.which == 99) { this.centerSlide(); }; //'c'
    }).bind(this));
    $(this.controls.zoomout_button).bind('click', (function() { this.zoomOut(); }).bind(this)).addClass('zoomout_button').attr('src', 'images/viewshrink.png');
    $(this.controls.zoomin_button).bind('click', (function() { this.zoomIn(); }).bind(this)).addClass('zoomin_button').attr('src', 'images/viewmag.png');
    $(this.controls).css({'top': $(this.container).position().top, 'left': $(this.container).position().left});
    $(this.controls).draggable();
    this.currentsize = "medium";
    this.currentslide = 0;
};

Dase.slideshow.prototype.CONTAINER_CLASS = 'dase_slideshow';
Dase.slideshow.prototype.CONTROLS_CLASS = 'dase_slideshow_controls';
Dase.slideshow.prototype.FADEIN = 200; // "fast", "slow" or time in ms
Dase.slideshow.prototype.FADEOUT = 200; // ditto
Dase.slideshow.prototype.VALID_SIZES = ['thumbnail', 'viewitem', 'small', 'medium', 'large', 'full'];
Dase.slideshow.prototype.autosize = function() {
    var instances = $('.'+this.CONTAINER_CLASS).length;
    var width = Math.floor($(window).width() / instances - $(window).width() * .05) - 10;
    var height = $(window).height();
    $(this.container).css({ 'height': height+'px', 'width': width+'px' });
    $(this.controls).css($(this.container).position());
    return this;
};
Dase.slideshow.prototype.current = function() {
    return this.slides[this.currentslide];
}
Dase.slideshow.prototype.show = function(img, fade) {
    if(img == undefined) {  img = this.current().media[this.currentsize]; }
    else img = typeof img == 'string' ? img : (typeof img == 'number' ? this.slides[img].media[this.currentsize] :  img.src);
    fade = fade !== false;
    fade = false;
    if(fade) { $(this.container).fadeOut(this.FADEOUT); }
    $(this.container).queue((function(i) {
        var img = i;
        this.image.src = img;
        $(this.container).dequeue();
    }).bind(this, img));
    if(fade) { $(this.container).fadeIn(this.FADEIN); }
    return this;
};
Dase.slideshow.prototype.updateControls = function() {
    $(this.controls.prev_button).css('display', this.currentslide > 0 ? 'inline' : 'none');
    $(this.controls.next_button).css('display', this.currentslide < this.slides.length - 1 ? 'inline' : 'none');
    $(this.controls.zoomout_button).css('display', (this.current().size_list.indexOf(this.currentsize) !== 0) ? 'inline' : 'none');
    $(this.controls.zoomin_button).css('display', this.current().size_list.indexOf(this.currentsize) < this.current().size_list.length - 2 ? 'inline' : 'none');
    this.controls.status.innerHTML = 'slide '+(this.currentslide + 1)+' of '+this.slides.length;
    var size_list = '';
    for(var s in this.slides[this.currentslide].media) { size_list = size_list + s; }
    this.controls.status.innerHTML = this.controls.status.innerHTML + '<br />' + this.current().annotation + '<br />' + this.currentsize;
    return this;
}
//slides are passes as { sizes: { small: { url: url, title: title ... }, medium: {...}, ...
Dase.slideshow.prototype.addSlide = function (slide) {
    for(var size in slide.media) { Dase.prefetch.img(slide.media[size]); }
    var available_sizes = []; for(var as in slide.media) { available_sizes.push(as); }
    slide.size_list = []; for(var vs in this.VALID_SIZES) { if(available_sizes.indexOf(this.VALID_SIZES[vs])) { slide.size_list.push(this.VALID_SIZES[vs]) } }
    this.slides.push(slide);
    return this;
};
Dase.slideshow.prototype.setCurrentSlide = function(i) {
    if(i < this.slides.length) this.currentslide = i;
    return this;
}
Dase.slideshow.prototype.setCurrentSize = function(size) {
    if(this.VALID_SIZES.indexOf(size)) { this.currentsize = size; }
    return this;
}
Dase.slideshow.prototype.advance = function() {
    if(this.currentslide < this.slides.length - 1) this.setCurrentSlide(this.currentslide + 1).updateControls().show();
    this.centerSlide();
    return this;
}
Dase.slideshow.prototype.retreat = function() {
    if(this.currentslide > 0) this.setCurrentSlide(this.currentslide - 1).updateControls().show();
    this.centerSlide();
    return this;
}
Dase.slideshow.prototype.zoomOut = function() {
    if(this.current().size_list.indexOf(this.currentsize) > 0) {
        this.setCurrentSize(this.current().size_list[this.current().size_list.indexOf(this.currentsize) - 1]).updateControls().show();
        //if($(this.image).position().top < 0) $(this.image).css('top', 0);
        /*if($(this.image).position().top < 0 || $(this.image).position().top > $(this.container).height() - $(this.image).height() 
            || $(this.image).position().left < 0 || $(this.image).position().left > $(this.container).width() - $(this.image).width()) {
            $(this.image).animate({'top': $(this.container).height()/2 - $(this.image).height()/2, 'left': $(this.container).width()/2 - $(this.image).width()/2 }, "fast");
        } */
        this.centerSlide();
    }
    return this;
}
Dase.slideshow.prototype.zoomIn = function() {
    if(this.current().size_list.indexOf(this.currentsize) < this.current().size_list.length - 2) {
        //$(this.image).animate({'width': Math.floor(1.5 * $(this.image).width()) }, "fast"); //this is awesome, but it doesn't work. fix this.
        this.setCurrentSize(this.current().size_list[this.current().size_list.indexOf(this.currentsize) + 1]).updateControls().show();
        this.centerSlide();
    }
    return this;
}
Dase.slideshow.prototype.centerSlide = function() {
    $(this.image).animate({'top': $(this.container).height()/2 - $(this.image).height()/2, 'left': $(this.container).width()/2 - $(this.image).width()/2 }, 25);
    //$(this.image).css({'top': $(this.container).height()/2 - $(this.image).height()/2, 'left': $(this.container).width()/2 - $(this.image).width()/2 });
}


Dase.slideshow.start = function(url,username,htpasswd) {
	var slides =  [];
//	$(document).bind('ready', function() {
//			if(window._slideshow && window._slideshow.url) {
			jQuery.ajax({
type: 'GET',
url: url,
dataType: 'json',
success: function(data) {
Dase.addClass(Dase.$('content'),'hide');
slides = data.items;
var dss = new Dase.slideshow('helloworld');
for(var slide in slides) { dss.addSlide(slides[slide]); }
dss.updateControls().show();
dss.centerSlide();
/*var dss2 = new Dase.slideshow('helloworld');
  for(var slide in slides) { dss2.addSlide(slides[slide]); }
  dss2.updateControls().show();
  dss2.centerSlide();
  dss.autosize();
  dss2.autosize();*/
},
username: username,
password: htpasswd 
});
//} else {
//	alert('Sorry, there was a problem fetching the slideshow.');
//}
//});
//var _slideshow = { url: 'http://jellybones.laits.utexas.edu/dss/js/tag.json', user: 'whatever', pass: 'whatever' };
//for (var s in slides) { if(typeof slides[s] !== "function") { for(var si in s.sizes) { Dase.prefetch.img(s.sizes[si]); } } }
for (var s in slides) {
   	if(typeof slides[s] !== "function") { 
	if (s.sizes['medium']) { 
		Dase.prefetch.img(s.sizes['medium']); 
	} 
	} 
}
}
