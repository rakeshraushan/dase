{extends file="layout.tpl"}

{block name="head-links"}
<link rel="edit" href="{$proposal->editLink}" />
<link rel="courses" href="{$courses}" />
<link rel="budget_items" href="{$budget_items}" />
{/block}

{block name="content"}
<div id="updateMsg" class="hide">proposal updated</div>
{assign var=metadata value="$proposal->metadata}
<div class="main">
	<div class="hide" id="unsaved">You have unsaved changes.</div>
	<div id="proposalForm"> 
		<div class="controls">
			<a href="{$previewLink}" id="previewLink" target="_blank">preview/submit</a>
		</div>
		<h1>Proposal: {$proposal->title}</h1>
		<input type="hidden" name="eid" value="{$person->serial_number}"/>
		<p>
		Please fill out each of the sections below completely. If
		a section does not apply to you, simply enter
		N/A.
		</p>
		<p>
		<strong>Important:</strong> You must click the
		“update” button at the end of each section to save
		any inputted information. The text box will flash a
		yellow color to confirm it has saved the text. When
		you are finished entering your proposal, please click
		“preview/submit” at the top right-hand corner of
		the page to check your work, print a copy and submit.
		</p>
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
		<p class="val">{$proposal->department}</p>
		</p>
		<p>
		<label for="project_type">Project Type</label>
		<p class="val">{$proposal->proposal_project_type.text}</p>
		</p>

		<p>
		<label for="proposal_collaborators"><span class="control">expand [+]</span>Additional Participants [optional]:</label>
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
		<label for="proposal_summary"><span class="control">expand [+]</span>Summary:</label>
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
		<label for="proposal_description"><span class="control">expand [+]</span>Description:</label>
		<div id="div_proposal_description" class="hide">
			<p class="instruction">A detailed description of the project with supporting argument, not to exceed 1,000 words. Be sure to include previous experience with similar projects and the outcome of those projects. Explain how you will gauge the success of this project, including: pedagogical goals, scope of impact, and projected date of completion. Please indicate if you anticipate that this project will require more than one year of funding.</p>
			{assign var=rows value=$proposal->proposal_description.text|count_words}
			<textarea id="proposal_description"rows="{$rows/11+2}"  name="proposal_description">{$proposal->proposal_description.text}</textarea>
			<p>
			<input class="proposal_description" action="{$proposal->proposal_description.edit}" type="submit" value="update"/>
			<!--
			<span id="description_word_count" class="mini">({$proposal->proposal_description.text|count_words} words)</span>
			-->
			</p>
		</div>
		</p>

		<p>
		<label for="courses"><span class="control">expand [+]</span>Students and Classes Served:</label>
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
		<label for="proposal_previous_funding"><span class="control">expand [+]</span>Previous Funding:</label>
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
		<label for="proposal_sta"><span class="control">expand [+]</span>Student Technology Assistant:</label>
		<div id="div_proposal_sta" class="hide textchoice">
			<p>If submitting a proposal for development of course materials, would you be interested in...</p>

			<p>Working with a student technology assistant <a href="http://www.laits.utexas.edu/sta" target="_blank">(STA)</a>? [<span class="current" id="sta_status">{$proposal->proposal_sta.text}</span>]
			<input type="radio" action="{$proposal->proposal_sta.edit}" id="sta_no" name="sta" {if 'no' == $proposal->proposal_sta.text}checked{/if} value="no"> no
			<input type="radio"  action="{$proposal->proposal_sta.edit}" id="sta_yes" name="sta" {if 'yes' == $proposal->proposal_sta.text}checked{/if} value="yes"> yes
			</p>
			<p class="instruction">read <a href="http://www.laits.utexas.edu/sta" target="_blank">more info about the STA program</a></p>
		</div>
		</p>

		<label for="proposal_faculty_workshop"><span class="control">expand [+]</span>Summer Faculty Workshop:</label>
		<div id="div_proposal_faculty_workshop" class="hide textchoice">
			<p>If submitting a proposal for development of course materials, would you be interested in...</p>

			<p>Participating in a 2-week summer faculty workshop? [<span class="current" id="workshop_status">{$proposal->proposal_faculty_workshop.text}</span>]
			<input type="radio"  action="{$proposal->proposal_faculty_workshop.edit}" id="workshop_no" name="workshop" {if 'no' == $proposal->proposal_faculty_workshop.text}checked{/if} value="no"> no
			<input type="radio"  action="{$proposal->proposal_faculty_workshop.edit}" id="workshop_yes" name="workshop" {if 'yes' == $proposal->proposal_faculty_workshop.text}checked{/if} value="yes"> yes
			</p>
			<p class="instruction">Special Note: If applying for the summer faculty workshop <strong>only</strong>, you may submit your proposal now. Your proposal is complete.</p>
		</div>
		</p>

		<p>
		<label for="proposal_professional_assistance"><span class="control">expand [+]</span>Professional Assistance:</label>
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
		<label for="proposal_renovation_description"><span class="control">expand [+]</span>Renovation Description:</label>
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
		<label for="proposal_budget_description"><span class="control">expand [+]</span>Budget Description:</label>
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
		<label for="budget"><span class="control">expand [+]</span>Itemized Budget:</label>

		<div id="div_budget" class="hide">
			<form action="proposal/{$proposal->serialNumber}/budget_items" method="post" id="budgetForm">
				<div id="budgetItemsList" class="hide"></div>
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
					<option>Salary: Summer 2010</option>
				</select>
				<label>quantity</label>
				<input size="6" name="budget_item_quantity" value="" type="text">
				<label>price per unit</label>
				<input size="6" name="budget_item_price" value="" type="text">
				<label>description</label>
				<textarea id="budget_item_description"  name="budget_item_description"></textarea>
				<p>
				<input id="add_budget_item" value="add budget item" type="submit">
				</p>
			</form>
		</div>
		</p>


		</div>
		<div class="deleteControl">
			<form action="{$proposal->editLink}" id="deleteProposal">
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

	<textarea class="javascript_template" id="proposal_budget_items_jst">
		<table class="listing" id="budget_items_table">
			<tr>
				<th></th>
				<th>type</th>
				<th>description</th>
				<th>quantity</th>
				<th>price per unit</th>
				<th>total</th>
			</tr>
			{literal}
			{for item in items}
			<tr>
				<td><a href="${item.edit}" class="delete">delete</a></td>
				<td>${item.metadata.budget_item_type}</td>
				<td>${item.metadata.budget_item_description}</td>
				<td>${item.metadata.budget_item_quantity}</td>
				<td>$${item.metadata.budget_item_price}</td>
				<td>$${item.metadata.total}</td>
			</tr>
			{/for}
			<tr>
				<td colspan="5">grand total:</td>
				<td>$${grand_total}</td>
			</tr>
			{/literal}
		</table>
	</textarea>

	{/block}

