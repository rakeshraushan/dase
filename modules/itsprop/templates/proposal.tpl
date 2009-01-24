{extends file="layout.tpl"}

{block name="head-links"}
<link rel="edit" href="{$proposal->editLink}" />
{/block}

{block name="content"}
<h1>Proposal: {$proposal->title}</h1>
<div class="main">
	<form id="proposalForm" method="post" >
		<input type="hidden" name="eid" value="{$person->serial_number}"/>
		<p>
		<label for="proposal_title">Proposal Title</label>
		<p class="val">{$proposal->proposal_name}</p>
		</p>
		<p>
		<label for="name">Proposer Name</label>
		<p class="val">{$person->person_name}</p>
		</p>
		<p>
		<label for="Department">Department</label>
		{foreach item=plink from=$person->parentLinks}
		{if 'department' == $plink.item_type}
		{assign var=dept_title value=$plink.title}
		{/if}
		{/foreach}	
		<p class="val">{$dept_title}</p>
		</p>
		<p>
		<label for="project_type">Project Type</label>
		<p class="val">{$proposal->proposal_project_type}</p>
		</p>

		<p>
		<label for="proposal_collaborators"><span class="control">[+/-]</span>Additional Participants [optional]:</label>
		<div id="proposal_collaborators" class="hide">
			<p class="instruction">Enter name(s) of additional participants, co-proposers, or principal investigator (if different from proposer).</p>
			<textarea name="proposal_collaborators">{$proposal->proposal_collaborators}</textarea>
		</div>
		</p>

		<p>
		<label for="proposal_summary"><span class="control">[+/-]</span>Summary:</label>
		<div id="proposal_summary" class="hide">
			<p class="instruction">A one paragraph summary of the project and supporting argument, not to exceed 150 words:</p>
			<textarea name="proposal_summary">{$proposal->proposal_summary}</textarea>
		</div>
		</p>

		<p>
		<label for="proposal_description"><span class="control">[+/-]</span>Description:</label>
		<div id="proposal_description" class="hide">
			<p class="instruction">A detailed description of the project with supporting argument, not to exceed 1,000 words. Be sure to include previous experience with similar projects and the outcome of those projects. Explain how you will gauge the success of this project, including: pedagogical goals, scope of impact, and projected date of completion. Please indicate if you anticipate that this project will require more than one year of funding.</p>
			<textarea name="proposal_description">{$proposal->proposal_description}</textarea>
		</div>
		</p>

		<p>
		<label for="courses"><span class="control">[+/-]</span>Students and Classes Served:</label>
		<div id="courses" class="hide">
			----------------------------------------
		</div>
		</p>

		<p>
		<label for="proposal_previous_funding"><span class="control">[+/-]</span>Previous Funding:</label>
		<div id="proposal_previous_funding" class="hide">
			<p class="instruction">Did you receive funding in prior years for this particular project? If so, when? Please add justification why funding should continue.</p>
			<textarea name="proposal_previous_funding">{$proposal->proposal_previous_funding}</textarea>
		</div>
		</p>

		<p>
		<label for="proposal_sta"><span class="control">[+/-]</span>Student technology assistant/summer faculty workshop:</label>
		<div id="proposal_sta" class="hide">
			<p>If submitting a proposal for development of course materials, would you be interested in...</p>

			<p>Working with a student technology assistant (STA)?
			<input type="radio" name="sta" value="no"> no
			<input type="radio" name="sta" value="yes"> yes
			</p>
			<p>Participating in a 2-week summer faculty workshop?
			<input type="radio" name="workshop" value="no"> no
			<input type="radio" name="workshop" value="yes"> yes
			</p>
			<p class="instruction">Special Note: If applying for the summer faculty workshop <strong>only</strong>, you may submit your proposal now. Your proposal is complete.</p>
		</div>
		</p>

		<p>
		<label for="proposal_professional_assistance"><span class="control">[+/-]</span>Professional Assistance:</label>
		<div id="proposal_professional_assistance" class="hide">
			<p class="instruction">Enter a description of any professional assistance requested from Liberal Arts Instructional Technology Services (LAITS).</p>
			<textarea name="proposal_professional_assistance">{$proposal->proposal_professional_assistance}</textarea>
		</div>
		</p>

		<p>
		<label for="proposal_renovation_description"><span class="control">[+/-]</span>Renovation Description:</label>
		<div id="proposal_renovation_description" class="hide">
			<p class="instruction">Include renovation schedules and costs, including electrical, lighting, furniture, and architectural changes. If you do not have an official renovation cost estimate, describe as best you can the nature and scope of the proposed work.</p>
			<textarea name="proposal_renovation_description">{$proposal->proposal_renovation_description}</textarea>
		</div>
		</p>

		<p>
		<label for="proposal_budget_description"><span class="control">[+/-]</span>Budget Description:</label>
		<div id="proposal_budget_description" class="hide">
			<p class="instruction">Include budget schedules and costs, including electrical, lighting, furniture, and architectural changes. If you do not have an official budget cost estimate, describe as best you can the nature and scope of the proposed work.</p>
			<textarea name="proposal_budget_description">{$proposal->proposal_budget_description}</textarea>
		</div>
		</p>

		<p>
		<label for="budget"><span class="control">[+/-]</span>Itemized Budget:</label>
		<div id="budget" class="hide">
			------------------------------------------
		</div>
		</p>

		
	</form>
	<form action="{$proposal->editLink}" id="delete_proposal">
		<input type="submit" value="delete this proposal"/>
	</form>
</div>
{/block}

