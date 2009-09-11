{extends file="layout.tpl"}

{block name="main"}
<div class="pagetitle">Plugins</div>
<p>The following browser plug-ins may be required for viewing the resources:</p>
<ul id="plugins">
	<li>
	<a href="http://www.macromedia.com/software/flashplayer/" target="_blank"><img src="{$module_root}images/flash.gif" alt="download Flash Player"></a>
	</li>
	<li>
	<a href="http://www.apple.com/quicktime/download/" target="_blank"><img src="{$module_root}images/qt.gif" alt="download Quicktime"></a>
	</li>

	<li>
	<a href="http://www.microsoft.com/windows/windowsmedia/default.aspx" target="_blank"><img src="{$module_root}images/wmp.gif" alt="download Windows Media Player"></a>
	</li>

	<li>
	<a href="http://www.real.com/" target="_blank"><img src="{$module_root}images/real.gif" alt="download Real Player"></a>
	</li>
	<li>
	<a href="http://www.java.com/en/download/manual.jsp" target="_blank"><img src="{$module_root}images/java_icon.gif" alt="download Java Runtime Environment"></a>
	</li>
	<li>
	<a href="http://www.macromedia.com/software/shockwaveplayer" target="_blank"><img src="{$module_root}images/shockwave_icon.gif" alt="download Shockwave Player"></a>
	</li>
</ul>
	{/block}

