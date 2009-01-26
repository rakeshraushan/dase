{extends file="layout.tpl"}

{block name="head-links"}
<link rel="edit" href="{$proposal->editLink}" />
<link rel="courses" href="{$courses}" />
<link rel="budget_items" href="{$budget_items}" />
{/block}

{block name="content"}
<h1>Proposal: {$proposal->title}</h1>
{assign var=metadata value="$proposal->metadata}
<div class="main">
	<div id="proposalForm">
		<input type="hidden" name="eid" value="{$person->serial_number}"/>
		<p>
		<label for="proposal_title">Proposal Title</label>
		<p class="val">{$proposal->proposal_name.text}</p>
		</p>
		<p>
		<label for="name">Proposer Name</label>
		<p class="val">{$person->person_name.text}</p>
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
		<p class="val">{$proposal->proposal_project_type.text}</p>
		</p>

		<p>
		<label for="proposal_collaborators"><span class="control">[+/-]</span>Additional Participants [optional]:</label>
		<div id="div_proposal_collaborators" class="hide">
			<p class="instruction">Enter name(s) of additional participants, co-proposers, or principal investigator (if different from proposer).</p>
			{assign var=rows value=$proposal->proposal_collaborators.text|count_words}
			<textarea id="proposal_collaborators" rows="{$rows/11+2}" name="proposal_collaborators">{$proposal->proposal_collaborators.text}</textarea>
			<p>
			<input class="proposal_collaborators" action="{$proposal->proposal_collaborators.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="proposal_summary"><span class="control">[+/-]</span>Summary:</label>
		<div id="div_proposal_summary" class="hide">
			<p class="instruction">A one paragraph summary of the project and supporting argument, not to exceed 150 words:</p>
			{assign var=rows value=$proposal->proposal_summary.text|count_words}
			<textarea id="proposal_summary" rows="{$rows/11+2}" name="proposal_summary">{$proposal->proposal_summary.text}</textarea>
			<p>
			<input class="proposal_summary" action="{$proposal->proposal_summary.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="proposal_description"><span class="control">[+/-]</span>Description:</label>
		<div id="div_proposal_description" class="hide">
			<p class="instruction">A detailed description of the project with supporting argument, not to exceed 1,000 words. Be sure to include previous experience with similar projects and the outcome of those projects. Explain how you will gauge the success of this project, including: pedagogical goals, scope of impact, and projected date of completion. Please indicate if you anticipate that this project will require more than one year of funding.</p>
			{assign var=rows value=$proposal->proposal_description.text|count_words}
			<textarea id="proposal_description"rows="{$rows/11+2}"  name="proposal_description">{$proposal->proposal_description.text}</textarea>
			<p>
			<input class="proposal_description" action="{$proposal->proposal_description.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="courses"><span class="control">[+/-]</span>Students and Classes Served:</label>
		<div id="div_courses" class="hide">
			<form action="proposal/{$proposal->serialNumber}/courses" method="post" id="courseForm">
				<div id="classesList" class="hide"></div>
				<input type="hidden" name="proposal" value="{$app_root}item/itsprop/{$proposal->serialNumber}" />
				<label for="course_title">title</label>
				<input type="text" name="course_title" value="" />
				<label for="course_number">course number</label>
				<input type="text" size="6" name="course_number" value="" />
				<label for="course_enrollment">enrollment</label>
				<input type="text" size="6" name="course_enrollment" value="" />
				<label for="course_frequency">frequency</label>
				<select name="course_frequency">
					<option value="">choose one:</option>
					<option value="">----------------------------</option>
					<option>once, every other year</option>
					<option>one semester per year</option>
					<option>two semesters per year</option>
					<option>both long semesters plus a summer session</option>
					<option>both long semesters and both summer sessions</option>
				</select>
				<p>
				<input id="add_class" type="submit" value="add" />
				</p>
			</form>
		</div>
		</p>

		<p>
		<label for="proposal_previous_funding"><span class="control">[+/-]</span>Previous Funding:</label>
		<div id="div_proposal_previous_funding" class="hide">
			<p class="instruction">Did you receive funding in prior years for this particular project? If so, when? Please add justification why funding should continue.</p>
			{assign var=rows value=$proposal->proposal_previous_funding.text|count_words}
			<textarea id="proposal_previous_funding" rows="{$rows/11+2}" name="proposal_previous_funding">{$proposal->proposal_previous_funding.text}</textarea>
			<p>
			<input class="proposal_previous_funding" action="{$proposal->proposal_previous_funding.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="proposal_sta"><span class="control">[+/-]</span>Student technology assistant/summer faculty workshop:</label>
		<div id="div_proposal_sta" class="hide">
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
		<div id="div_proposal_professional_assistance" class="hide">
			<p class="instruction">Enter a description of any professional assistance requested from Liberal Arts Instructional Technology Services (LAITS).</p>
			{assign var=rows value=$proposal->proposal_professional_assistance.text|count_words}
			<textarea id="proposal_professional_assistance" rows="{$rows/11+2}" name="proposal_professional_assistance">{$proposal->proposal_professional_assistance.text}</textarea>
			<p>
			<input class="proposal_professional_assistance" action="{$proposal->proposal_professional_assistance.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="proposal_renovation_description"><span class="control">[+/-]</span>Renovation Description:</label>
		<div id="div_proposal_renovation_description" class="hide">
			<p class="instruction">Include renovation schedules and costs, including electrical, lighting, furniture, and architectural changes. If you do not have an official renovation cost estimate, describe as best you can the nature and scope of the proposed work.</p>
			{assign var=rows value=$proposal->proposal_renovation_description.text|count_words}
			<textarea id="proposal_renovation_description" rows="{$rows/11+2}" name="proposal_renovation_description">{$proposal->proposal_renovation_description.text}</textarea>
			<p>
			<input class="proposal_renovation_description" action="{$proposal->proposal_renovation_description.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="proposal_budget_description"><span class="control">[+/-]</span>Budget Description:</label>
		<div id="div_proposal_budget_description" class="hide">
			<p class="instruction">Include budget schedules and costs, including electrical, lighting, furniture, and architectural changes. If you do not have an official budget cost estimate, describe as best you can the nature and scope of the proposed work.</p>
			{assign var=rows value=$proposal->proposal_budget_description.text|count_words}
			<textarea id="proposal_budget_description" rows="{$rows/11+2}" name="proposal_budget_description">{$proposal->proposal_budget_description.text}</textarea>
			<p>
			<input class="proposal_budget_description" action="{$proposal->proposal_budget_description.edit}" type="submit" value="update"/>
			</p>
		</div>
		</p>

		<p>
		<label for="budget"><span class="control">[+/-]</span>Itemized Budget:</label>

		<div id="div_budget" class="hide">
			<form action="proposal/{$proposal->serialNumber}/courses" method="post" id="courseForm">
				<div id="budget" class="hide"></div>
				<input type="hidden" name="proposal" value="{$app_root}item/itsprop/{$proposal->serialNumber}" />
				<label>item type</label>
				<select name="budget_item_type">
					<option value="" selected="selected">Select one ...</option>
					<option value="">---------------------------</option>
					<option>Computer equipment</option>
					<option>Special equipment</option>
					<option>Software</option>
					<option>Supplies and misc</option>
					<option>Wages: graduate</option>
					<option>Wages: undergraduate</option>
					<option>Wages: workstudy</option>
					<option>Salary: FY 2009-2010</option>
					<option>Salary: Summer 2010</option>
				</select>
				<label>quantity</label>
				<input size="6" name="budget_item_quantity" value="" type="text">
				<label>price</label>
				<input size="6" name="budget_item_price" value="" type="text">
				<label>description</label>
				<textarea id="budget_item_description"  name="budget_item_description"></textarea>
				<p>
				<input id="add_budget_item" value="add budget item" type="submit">
				</p>
			</div>
			</p>


		</div>
		<div id="deleteControl">
			<form action="{$proposal->editLink}" id="delete_proposal">
				<input type="submit" value="delete this proposal"/>
			</form>
		</div>
	</div>
	<textarea class="javascript_template" id="proposal_courses_jst">
		<ul id="courses">
			{literal}
			{for c in classes}
			<li>${c.metadata.title}
			(${c.metadata.course_number})
			[${c.metadata.course_enrollment} students ${c.metadata.course_frequency}]
			<a href="${c.edit}" class="delete">delete</a>
			</li>
			{/for}
			{/literal}
		</ul>
	</textarea>
	{/block}

