Dase.slideshow = {};
Dase.slideshow.NAME = "Dase Default Slideshow";
Dase.slideshow.sizes = ['medium','large','full','small','viewitem','thumbnail'];
Dase.slideshow.start = function(url,username,htpasswd) {
	Dase.slideshow.defaultSize = 'medium';
	document.body.innerHTML = '<h1 id="ajaxMsg">loading slideshow....</h1>';
	slides = [];
	var rendering_func = function(json) {
		document.body.innerHTML = '';
		if (json.background) {
			document.body.className = json.background;
		} else {
			document.body.className = 'black';
		}
		var title_el = Dase.createElem(document.body,json.name,'h1',null,'slideshowTitle');
		Dase.createElem(document.body,'[x]','a',null,'slideshowExit').href = window.location;;
		var tally = Dase.createElem(title_el,null,'span',null,'num_of_num');
		var sizes = Dase.createElem(document.body,null,'div',null,'slideshowSizes');
		var controls = Dase.createElem(document.body,null,'div',null,'slideshowControls');
		var prev = Dase.createElem(controls,'< prev','a');
		prev.href = '#';
		Dase.createElem(controls,' | ','span');
		for(var i=0;i<json.items.length;i++) { 
			//preload 20 medium images
			if (i < 20) {
				var imgObj = new Image();
				imgObj.src = json.items[i].media.medium;
			}
			slides[slides.length] = json.items[i].media;
		}
		var next = Dase.createElem(controls,'next >','a');
		next.href = '#';
		Dase.createElem(document.body,null,'div','spacer');
		var container = Dase.createElem(document.body,null,'div',null,'slideshowContainer');
		var img = Dase.createElem(container,null,'img');
		Dase.slideshow.viewSlide(slides,0,img,prev,next);
	};
	Dase.getJSON(url,rendering_func,null,null,username,htpasswd);
};

Dase.slideshow.viewSlide = function(slides,num,img,prev,next) {
	img.src = slides[num][Dase.slideshow.defaultSize];
	if (!slides[num][Dase.slideshow.defaultSize]) {
		for (var i=0;i<Dase.slideshow.sizes.length;i++) {
			if (slides[num][Dase.slideshow.sizes[i]]) {
				img.src = slides[num][Dase.slideshow.sizes[i]];
				break;
			}
		}
	}
	var sizes = Dase.$('slideshowSizes');
	sizes.innerHTML = null;
	var tally = Dase.$('num_of_num');
	var place = num+1;
	tally.innerHTML = ': '+place+' of '+slides.length;
	for (size in slides[num]) {
		if (size != 'thumbnail' && size != 'viewitem') {
			var size_link = Dase.createElem(sizes,size,'a');
			size_link.href = slides[num][size];
			size_link.size = size;
			size_link.onclick = function() {
				Dase.slideshow.defaultSize = this.size;
				this.className = 'currentSize';
				var cur = Dase.$('current');
				if (cur) {
					cur.className = '';
				}
				this.id = 'current';
				img.src =this.href;
				return false;
			}
			Dase.createElem(sizes,' | ','span');
			if (size_link.size == Dase.slideshow.defaultSize) {
				size_link.className = 'currentSize';
				size_link.id = 'current';
			} else {
				size_link.className = '';
			}
		}
	}
	prev.onclick = function() {
		if (0 == num) {
			prev_num = slides.length - 1;
		} else {
			prev_num = num - 1;
		}
		Dase.slideshow.viewSlide(slides,prev_num,img,prev,next);
		return false;
	}
	next.onclick = function() {
		if (num == (slides.length -1)) {
			next_num = 0; 
		} else {
			next_num = num + 1;
		}
		Dase.slideshow.viewSlide(slides,next_num,img,prev,next);
		return false;
	}
	img.onclick = next.onclick;
};
