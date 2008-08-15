var colors = new Array();
colors[0] = "#404072";
colors[1] = "#515672";
colors[2] = "#656873";
colors[3] = "#7c7c7c";
colors[4] = "#8e9094";
colors[17] = "#663333";
colors[16] = "#814547";
colors[15] = "#996666";
colors[14] = "#ae7577";
colors[5] = "#cc9999";
colors[18] = "#c20000";
colors[11] = "#cb5050";
colors[12] = "#d97676";
colors[13] = "#ef9090";
colors[6] = "#fdb7b7";
colors[19] = "#e7651a";
colors[10] = "#e78349";
colors[9] = "#e6986b";
colors[8] = "#e6aa87";
colors[7] = "#e7c4af";
colors[20] = "#d4d000";
colors[21] = "#dcda5f";
colors[22] = "#e1e09b";
colors[23] = "#eae9b9";
colors[24] = "#edecc3";

var color_table = '<table id="colors"><tr><td id="cell_16">&nbsp;</td><td id="cell_15">&nbsp;</td><td id="cell_14">&nbsp;</td><td id="cell_13">&nbsp;</td><td id="cell_12">&nbsp;</td></tr><tr><td id="cell_17">&nbsp;</td><td id="cell_4">&nbsp;</td><td id="cell_3">&nbsp;</td><td id="cell_2">&nbsp;</td><td id="cell_11">&nbsp;</td></tr><tr><td id="cell_18">&nbsp;</td><td id="cell_5">&nbsp;</td><td id="cell_0">&nbsp;</td><td id="cell_1">&nbsp;</td><td id="cell_10">&nbsp;</td></tr><tr><td id="cell_19">&nbsp;</td><td id="cell_6">&nbsp;</td><td id="cell_7">&nbsp;</td><td id="cell_8">&nbsp;</td><td id="cell_9">&nbsp;</td></tr><tr><td id="cell_20">&nbsp;</td><td id="cell_21">&nbsp;</td><td id="cell_22">&nbsp;</td><td id="cell_23">&nbsp;</td><td id="cell_24">&nbsp;</td></tr></table>';

var color_table = '<table id="colors"><tr><td id="cell_0">&nbsp;</td><td id="cell_1">&nbsp;</td><td id="cell_2">&nbsp;</td><td id="cell_3">&nbsp;</td><td id="cell_4">&nbsp;</td><td id="cell_5">&nbsp;</td><td id="cell_6">&nbsp;</td><td id="cell_7">&nbsp;</td><td id="cell_8">&nbsp;</td><td id="cell_9">&nbsp;</td><td id="cell_10">&nbsp;</td><td id="cell_11">&nbsp;</td><td id="cell_12">&nbsp;</td><td id="cell_13">&nbsp;</td><td id="cell_14">&nbsp;</td><td id="cell_15">&nbsp;</td><td id="cell_16">&nbsp;</td><td id="cell_17">&nbsp;</td><td id="cell_18">&nbsp;</td><td id="cell_19">&nbsp;</td><td id="cell_20">&nbsp;</td><td id="cell_21">&nbsp;</td><td id="cell_22">&nbsp;</td><td id="cell_23">&nbsp;</td><td id="cell_24">&nbsp;</td></tr></table>';

var index = 0;
var cycle_length = 0;
function rotateColor() {
	document.getElementById("cell_0").style.background=colors[(index + 0) % colors.length];
	document.getElementById("cell_1").style.background=colors[(index + 1) % colors.length];
	document.getElementById("cell_2").style.background=colors[(index + 2) % colors.length];
	document.getElementById("cell_3").style.background=colors[(index + 3) % colors.length];
	document.getElementById("cell_4").style.background=colors[(index + 4) % colors.length];
	document.getElementById("cell_5").style.background=colors[(index + 5) % colors.length];
	document.getElementById("cell_6").style.background=colors[(index + 6) % colors.length];
	document.getElementById("cell_7").style.background=colors[(index + 7) % colors.length];
	document.getElementById("cell_8").style.background=colors[(index + 8) % colors.length];
	document.getElementById("cell_9").style.background=colors[(index + 9) % colors.length];
	document.getElementById("cell_10").style.background=colors[(index + 10) % colors.length];
	document.getElementById("cell_11").style.background=colors[(index + 11) % colors.length];
	document.getElementById("cell_12").style.background=colors[(index + 12) % colors.length];
	document.getElementById("cell_13").style.background=colors[(index + 13) % colors.length];
	document.getElementById("cell_14").style.background=colors[(index + 14) % colors.length];
	document.getElementById("cell_15").style.background=colors[(index + 15) % colors.length];
	document.getElementById("cell_16").style.background=colors[(index + 16) % colors.length];
	document.getElementById("cell_17").style.background=colors[(index + 17) % colors.length];
	document.getElementById("cell_18").style.background=colors[(index + 18) % colors.length];
	document.getElementById("cell_19").style.background=colors[(index + 19) % colors.length];
	document.getElementById("cell_20").style.background=colors[(index + 20) % colors.length];
	document.getElementById("cell_21").style.background=colors[(index + 21) % colors.length];
	document.getElementById("cell_22").style.background=colors[(index + 22) % colors.length];
	document.getElementById("cell_23").style.background=colors[(index + 23) % colors.length];
	document.getElementById("cell_24").style.background=colors[(index + 24) % colors.length];
	index = (index + 1) % colors.length;
	cycle_length = 200;
	setTimeout("rotateColor()", cycle_length);
}

function startColors(id) {
	var elem=document.getElementById(id);
	elem.innerHTML = color_table;
	rotateColor();
}

function stopColors(id) {
	var elem=document.getElementById(id);
	elem.innerHTML = '';
}

