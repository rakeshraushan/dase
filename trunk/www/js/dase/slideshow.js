Dase.slideshow = {};
Dase.slideshow.NAME = "Dase Default Slideshow";
Dase.slideshow.colors = {'black':'black','white':'white'};
Dase.slideshow.sizes = ['medium','large','full','small','viewitem','thumbnail'];
Dase.slideshow.start = function(url,username,htpasswd) {
	Dase.slideshow.defaultSize = 'medium';
	document.body.innerHTML = '<h1 id="ajaxMsg">loading slideshow....</h1>';
	slides = [];
	var rendering_func = function(json) {
		document.body.innerHTML = '';
//		if (json.background) {
//			document.body.className = json.background;
//		} else {
	    document.body.className = 'black';
//		}
		var title_el = Dase.createElem(document.body,json.name,'h1',null,'slideshowTitle');
		Dase.createElem(document.body,'[x]','a',null,'slideshowExit').href = window.location;;
		var tally = Dase.createElem(title_el,null,'span',null,'num_of_num');
		var sizes = Dase.createElem(document.body,null,'div',null,'slideshowSizes');
		var bgcolor = Dase.createElem(document.body,null,'div',null,'bgColor');
		var controls = Dase.createElem(document.body,null,'div',null,'slideshowControls');
		var prev = Dase.createElem(controls,'< prev','a');
		prev.href = '#';
		Dase.createElem(controls,' | ','span');
		for(var i=0;i<json.items.length;i++) { 
			//preload 20 medium images
			if (i < 20) {
				var imgObj = new Image();
				if (json.items[i].media && json.items[i].media.medium) {
					imgObj.src = json.items[i].media.medium;
				}
			}
			slides[slides.length] = json.items[i];
		}
		var next = Dase.createElem(controls,'next >','a');
		next.href = '#';
		Dase.createElem(document.body,null,'div','spacer');
		var container = Dase.createElem(document.body,null,'div',null,'slideshowContainer');
		var img = Dase.createElem(container,null,'img');
		var annotation = Dase.createElem(container,null,'p','annotation');
		Dase.slideshow.viewSlide(slides,0,img,annotation,prev,next);
	};
	Dase.getJSON(url,rendering_func,null,null,username,htpasswd);
};

Dase.slideshow.minifyLinks = function() {
	var sizes = Dase.$('slideshowSizes');
	var links = sizes.getElementsByTagName('a');
	for (var i=0;i<links.length;i++) {
		links[i].className = '';
	}
}

Dase.slideshow.viewSlide = function(slides,num,img,annotation,prev,next) {
	img.src = slides[num]['media'][Dase.slideshow.defaultSize];
	if (!slides[num]['media'][Dase.slideshow.defaultSize]) {
		for (var i=0;i<Dase.slideshow.sizes.length;i++) {
			if (slides[num]['media'][Dase.slideshow.sizes[i]]) {
				img.src = slides[num]['media'][Dase.slideshow.sizes[i]];
				break;
			}
		}
	}
	var sizes = Dase.$('slideshowSizes');
	sizes.innerHTML = null;
	var tally = Dase.$('num_of_num');
	var place = num+1;
	tally.innerHTML = ': '+place+' of '+slides.length;
	annotation.innerHTML = null;
	if (slides[num]['annotation']) {
		annotation.innerHTML = slides[num]['annotation'];
	}
	var colors = Dase.$('bgColor');
	colors.innerHTML = null;
	//bar = Dase.createElem(colors,'background: ','span');
	var bar;
	for (color in Dase.slideshow.colors) {
		var color_link = Dase.createElem(colors,color,'a');
		color_link.href = '#';
		color_link.color = color;
		color_link.onclick = function() {
			document.body.className = this.color;
			return false;
		};
		bar = Dase.createElem(colors,' | ','span');
	}
	bar.style.display = 'none';
	for (size in slides[num]['media']) {
		if (size != 'thumbnail' && size != 'viewitem') {
			var size_link = Dase.createElem(sizes,size,'a');
			size_link.href = slides[num]['media'][size];
			size_link.size = size;
			size_link.onclick = function() {
				Dase.slideshow.defaultSize = this.size;
				Dase.slideshow.minifyLinks();
				this.className = 'currentSize';
				img.src =this.href;
				return false;
			}
			bar = Dase.createElem(sizes,' | ','span');
			if (size_link.size == Dase.slideshow.defaultSize) {
				size_link.className = 'currentSize';
			} else {
				size_link.className = '';
			}
		}
	}
	bar.style.display = 'none';
	prev.onclick = function() {
		if (0 == num) {
			prev_num = slides.length - 1;
		} else {
			prev_num = num - 1;
		}
		Dase.slideshow.viewSlide(slides,prev_num,img,annotation,prev,next);
		return false;
	}
	next.onclick = function() {
		if (num == (slides.length -1)) {
			next_num = 0; 
		} else {
			next_num = num + 1;
		}
		Dase.slideshow.viewSlide(slides,next_num,img,annotation,prev,next);
		return false;
	}
	img.onclick = next.onclick;
};
