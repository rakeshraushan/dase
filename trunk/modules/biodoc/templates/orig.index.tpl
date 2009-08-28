{extends file="layout.tpl"}

{block name="content}
  <div id="content">
	<div id="navigation">
	</div>
	<div id="navhr"><img src="biodoc_files/nav_hr.jpg" width="760" height="6" align="left"></div>


	<div id="ltmain">
	  <form id="unitForm" action="index.php" method="get">
		<input id="runMode" name="rm" value="search" type="hidden">
		<p>Browse:</p>
		<select id="unitFormSelect" name="txtUnit" style="width: 150px;">
		  <option selected="selected" value="0">--Select Unit--</option>
		  <option value="Animal Physiology">Animal Physiology</option>
		  <option value="Biodiversity and Systematics">Biodiversity and Systematics</option>
		  <option value="Cell Biology">Cell Biology</option>
		  <option value="Chemistry of Life">Chemistry of Life</option>
		  <option value="Development">Development</option>
		  <option value="Ecology">Ecology</option>
		  <option value="Evolution">Evolution</option>
		  <option value="Genetics">Genetics</option>
		  <option value="Metabolism">Metabolism</option>
		  <option value="Miscellaneous">Miscellaneous</option>
		  <option value="Plant Physiology">Plant Physiology</option>
		  <option value="The Cell">The Cell</option>
		</select><br>
		<span id="loading"></span><br>

		<select id="topicSelect" class="hide" name="txtTopic" style="width: 150px;">
		  <option selected="selected" value="0">--Select Topic--</option>
		  <option value="Acid/base Chemistry">Acid/base Chemistry</option>
		  <option value="Acid/Base Chemistry">Acid/Base Chemistry</option>
		  <option value="Animal Anatomy">Animal Anatomy</option>
		  <option value="Animal behavior">Animal behavior</option>
		  <option value="Animal Behavior">Animal Behavior</option>
		  <option value="Animal Biology">Animal Biology</option>
		  <option value="Animal Cells">Animal Cells</option>
		  <option value="Animals">Animals</option>
		  <option value="Asexual Reproduction">Asexual Reproduction</option>
		  <option value="Atoms">Atoms</option>
		  <option value="ATP">ATP</option>
		  <option value="Behavioral Ecology">Behavioral Ecology</option>
		  <option value="Bioethics">Bioethics</option>
		  <option value="Biogeochemistry">Biogeochemistry</option>
		  <option value="Biogeography">Biogeography</option>
		  <option value="Biology">Biology</option>
		  <option value="Calvin Cycle">Calvin Cycle</option>
		  <option value="Cancer">Cancer</option>
		  <option value="Carbohydrates">Carbohydrates</option>
		  <option value="Cardiovascular system">Cardiovascular system</option>
		  <option value="Cell Communication">Cell Communication</option>
		  <option value="Cell Cycle">Cell Cycle</option>
		  <option value="Cell Migration">Cell Migration</option>
		  <option value="Cell Structure">Cell Structure</option>
		  <option value="Cell Transport">Cell Transport</option>
		  <option value="Chemical Bonds">Chemical Bonds</option>
		  <option value="Chromosomes">Chromosomes</option>
		  <option value="Circulation">Circulation</option>
		  <option value="Classification">Classification</option>
		  <option value="Climate">Climate</option>
		  <option value="Cloning">Cloning</option>
		  <option value="Coevolution">Coevolution</option>
		  <option value="Community Ecology">Community Ecology</option>
		  <option value="Comparative Biology">Comparative Biology</option>
		  <option value="Competition">Competition</option>
		  <option value="Conservation Biology">Conservation Biology</option>
		  <option value="Cytoplasm">Cytoplasm</option>
		  <option value="Cytoskeleton">Cytoskeleton</option>
		  <option value="Developmental Genetics">Developmental Genetics</option>
		  <option value="Digestive System">Digestive System</option>
		  <option value="Disease">Disease</option>
		  <option value="DNA Repair">DNA Repair</option>
		  <option value="DNA structure">DNA structure</option>
		  <option value="Ecosystem Ecology">Ecosystem Ecology</option>
		  <option value="Endomembranes">Endomembranes</option>
		  <option value="Enzymes">Enzymes</option>
		  <option value="Eukaryotes">Eukaryotes</option>
		  <option value="Evolution of Development">Evolution of Development</option>
		  <option value="Excretion">Excretion</option>
		  <option value="Experimental Design">Experimental Design</option>
		  <option value="Fitness">Fitness</option>
		  <option value="Floral Anatomy">Floral Anatomy</option>
		  <option value="Fungi">Fungi</option>
		  <option value="Gas Exchange">Gas Exchange</option>
		  <option value="Gastrulation">Gastrulation</option>
		  <option value="Gene Expression">Gene Expression</option>
		  <option value="Gene Mapping">Gene Mapping</option>
		  <option value="Gene Regulation">Gene Regulation</option>
		  <option value="Genetic Drift">Genetic Drift</option>
		  <option value="Genetics">Genetics</option>
		  <option value="Genetic Technology">Genetic Technology</option>
		  <option value="Genomics">Genomics</option>
		  <option value="Global Change">Global Change</option>
		  <option value="Glycolysis">Glycolysis</option>
		  <option value="Growth">Growth</option>
		  <option value="Homeostasis">Homeostasis</option>
		  <option value="Hormones">Hormones</option>
		  <option value="Human Evolution">Human Evolution</option>
		  <option value="Human Populations">Human Populations</option>
		  <option value="Immunology">Immunology</option>
		  <option value="Krebs Cycle">Krebs Cycle</option>
		  <option value="LaboratoryTechniques">LaboratoryTechniques</option>
		  <option value="Laboratory Techniques">Laboratory Techniques</option>
		  <option value="Lab Techniques">Lab Techniques</option>
		  <option value="Life cycles">Life cycles</option>
		  <option value="Life Cycles">Life Cycles</option>
		  <option value="Life History">Life History</option>
		  <option value="Light Reaction">Light Reaction</option>
		  <option value="Light Reactions">Light Reactions</option>
		  <option value="Linkage">Linkage</option>
		  <option value="Lipid metabolism">Lipid metabolism</option>
		  <option value="Lipids">Lipids</option>
		  <option value="Locomotion">Locomotion</option>
		  <option value="Medicine">Medicine</option>
		  <option value="Meiosis">Meiosis</option>
		  <option value="Membranes">Membranes</option>
		  <option value="Mendelian Genetics">Mendelian Genetics</option>
		  <option value="Meristems">Meristems</option>
		  <option value="Microbial Genetics">Microbial Genetics</option>
		  <option value="Microbiology">Microbiology</option>
		  <option value="Microbiology Techniques">Microbiology Techniques</option>
		  <option value="Microscopy">Microscopy</option>
		  <option value="Mitosis">Mitosis</option>
		  <option value="Model Systems">Model Systems</option>
		  <option value="Molecular Evolution">Molecular Evolution</option>
		  <option value="Molecules">Molecules</option>
		  <option value="Monera">Monera</option>
		  <option value="Mutation">Mutation</option>
		  <option value="Natural Selection">Natural Selection</option>
		  <option value="Nervous System">Nervous System</option>
		  <option value="Nucleic acids">Nucleic acids</option>
		  <option value="Nucleic Acids">Nucleic Acids</option>
		  <option value="Nucleus">Nucleus</option>
		  <option value="Nutrition">Nutrition</option>
		  <option value="Oncology">Oncology</option>
		  <option value="Organelles">Organelles</option>
		  <option value="Organ Formation">Organ Formation</option>
		  <option value="Origin of Life">Origin of Life</option>
		  <option value="Osmoregulation">Osmoregulation</option>
		  <option value="Other">Other</option>
		  <option value="Oxidative Phosphorylation">Oxidative Phosphorylation</option>
		  <option value="Paleontology">Paleontology</option>
		  <option value="Pattern Formation">Pattern Formation</option>
		  <option value="Photosynthesis">Photosynthesis</option>
		  <option value="Phylogenetics">Phylogenetics</option>
		  <option value="Plant Anatomy">Plant Anatomy</option>
		  <option value="Plant Cells">Plant Cells</option>
		  <option value="Plant Defense">Plant Defense</option>
		  <option value="Plant Development">Plant Development</option>
		  <option value="Plants">Plants</option>
		  <option value="Population Dynamics">Population Dynamics</option>
		  <option value="Population Genetics">Population Genetics</option>
		  <option value="Predation">Predation</option>
		  <option value="Prokaryotes">Prokaryotes</option>
		  <option value="Proteins">Proteins</option>
		  <option value="Protista">Protista</option>
		  <option value="Replication">Replication</option>
		  <option value="Reproduction">Reproduction</option>
		  <option value="Ribosomes">Ribosomes</option>
		  <option value="Sensory system">Sensory system</option>
		  <option value="Sequencing">Sequencing</option>
		  <option value="Sex linkage">Sex linkage</option>
		  <option value="Sex Linkage">Sex Linkage</option>
		  <option value="Sexual Reproduction">Sexual Reproduction</option>
		  <option value="Speciation">Speciation</option>
		  <option value="Stem Cells">Stem Cells</option>
		  <option value="Support">Support</option>
		  <option value="Synthetic Biology">Synthetic Biology</option>
		  <option value="Transcription">Transcription</option>
		  <option value="Translation">Translation</option>
		  <option value="Transport">Transport</option>
		  <option value="Viruses">Viruses</option>
		  <option value="Water">Water</option>
		</select>

		<p>
		</p><center><img src="biodoc_files/side_hr.jpg"></center>
		<p>Search:</p>

		<input size="12" name="txtSearch" type="text">
		<br>
		ex. "meiosis" or "DNA" 
		<p></p>
		<p>    
		<input type="submit">
		</p>
	</form></div>
	<div id="navline"></div>


	<div id="mainlist">
	  <p>
	  <span class="pagetitle">Results </span>(15 records)


	  <strong>1</strong>
	  <a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=search&amp;txtSearch=&amp;txtUnit=Evolution&amp;txtTopic=0&amp;start=9">2</a>

	  <a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=search&amp;txtSearch=&amp;txtUnit=Evolution&amp;txtTopic=0&amp;start=9">next</a>
	  </p>

	  <p>Evolution &gt; 0 &gt;</p>
	  <table width="100%" border="0" cellpadding="1" cellspacing="3">

		<tbody><tr valign="middle">
			<td width="7%"></td>
			<td width="41%"><span class="tabletitle">Resource</span> </td>
			<td width="12%"><span class="tabletitle">Type</span> </td>
			<td width="10%"><span class="tabletitle">Details</span> </td>
			<td width="16%"><span class="tabletitle">Link</span> </td>
			<td width="14%"><span class="tabletitle">Level</span> </td>
		  </tr>
		  <tr>
			<td>
			  <img src="biodoc_files/02_Cell_Theev_058.gif">
			</td>
			<td>
			  <p><strong>The Evolution of Organelles</strong><br>
			  Reveiws the organelles present in cells
			  </p></td>
			  <td>
				<p>Animation</p>
			  </td>
			  <td>
				<p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371469">Details</a></p>
			  </td>
			  <td>
				<p><a href="http://www.sumanasinc.com/webcontent/anisamples/nonmajorsbiology/organelles.html" target="_blank">Go To Directly</a></p>
			  </td>
			  <td>
				<p>Intro</p>
			  </td>
			</tr>
			<tr>
			  <td>
				<img src="biodoc_files/04_Evol_Evolu_107.gif">
			  </td>
			  <td>
				<p><strong>Evolution Lab</strong><br>
				Lets user test effects of selection and mutation in evolution
				</p></td>
				<td>
				  <p>Exercise</p>
				</td>
				<td>
				  <p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371518">Details</a></p>
				</td>
				<td>
				  <p><a href="http://biologyinmotion.com/evol/index.html" target="_blank">Go To Directly</a></p>
				</td>
				<td>
				  <p>Intro</p>
				</td>
			  </tr>
			  <tr>
				<td>
				  <img src="biodoc_files/03_DNAm_Theco_166.gif">
				</td>
				<td>
				  <p><strong>The Consequence of Inversion</strong><br>
				  shows mechanisms of how DNA is inverted in a chromosome
				  </p></td>
				  <td>
					<p>Animation</p>
				  </td>
				  <td>
					<p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371566">Details</a></p>
				  </td>
				  <td>
					<p><a href="http://highered.mcgraw-hill.com/sites/0072437316/student_view0/chapter20/animations.html" target="_blank">Go To Directly</a></p>
				  </td>
				  <td>
					<p>High School/Intro</p>
				  </td>
				</tr>
				<tr>
				  <td>
					<img src="biodoc_files/04_Evol_Becom_252.gif">
				  </td>
				  <td>
					<p><strong>Becoming Human</strong><br>
					Gives interative timeline of humans
					</p></td>
					<td>
					  <p>Interactive Animation</p>
					</td>
					<td>
					  <p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371652">Details</a></p>
					</td>
					<td>
					  <p><a href="http://www.becominghuman.org/" target="_blank">Go To Directly</a></p>
					</td>
					<td>
					  <p>Intro</p>
					</td>
				  </tr>
				  <tr>
					<td>
					  <img src="biodoc_files/03_YChr_SxDtm_019.gif">
					</td>
					<td>
					  <p><strong>Sex Determination: Evolution of Y chromosome</strong><br>
					  Evolution of Y chromosome
					  </p></td>
					  <td>
						<p>Animation</p>
					  </td>
					  <td>
						<p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371797">Details</a></p>
					  </td>
					  <td>
						<p><a href="http://www.hhmi.org/biointeractive/animations/y_evol/y_frames.htm" target="_blank">Go To Directly</a></p>
					  </td>
					  <td>
						<p>Adv College</p>
					  </td>
					</tr>
					<tr>
					  <td>
						<img src="biodoc_files/04_evol_phylo_178.gif">
					  </td>
					  <td>
						<p><strong>Phylogenetic Tree</strong><br>
						Explains the evolution of different species of butterfly from  a common ancestor.
						</p></td>
						<td>
						  <p>Animation</p>
						</td>
						<td>
						  <p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371801">Details</a></p>
						</td>
						<td>
						  <p><a href="http://www.biosci.utexas.edu/IB/faculty/woods/301M/PhylogeneticTrees.swf" target="_blank">Go To Directly</a></p>
						</td>
						<td>
						  <p>Adv College</p>
						</td>
					  </tr>
					  <tr>
						<td>
						  <img src="biodoc_files/6_FigWa_2.jpg">
						</td>
						<td>
						  <p><strong>Fig, Wasp Mutualism</strong><br>
						  26 min. Film explaining Fig/Wasp Mutualism
						  </p></td>
						  <td>
							<p>Video</p>
						  </td>
						  <td>
							<p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371973">Details</a></p>
						  </td>
						  <td>
							<p><a href="http://amap.cirad.fr/video/Figuier_anglais.ram" target="_blank">Go To Directly</a></p>
						  </td>
						  <td>
							<p>AdvCollege</p>
						  </td>
						</tr>
						<tr>
						  <td>
							<img src="biodoc_files/6_Sexual_12.jpg">
						  </td>
						  <td>
							<p><strong>Sexual Selection</strong><br>
							Illustrates sexual selection in Treefrog
							</p></td>
							<td>
							  <p>Video</p>
							</td>
							<td>
							  <p><a href="http://uts.cc.utexas.edu/%7Ejeac002/index.php?rm=get_item&amp;serial_number=000371992">Details</a></p>
							</td>
							<td>
							  <p><a href="http://www.midwestfrogs.com/media/treefrogchoice.wmv" target="_blank">Go To Directly</a></p>
							</td>
							<td>
							  <p>Intermediate College</p>
							</td>
						  </tr>
					  </tbody></table>
					</div>

					<div id="footer">
					  <img src="biodoc_files/footer_hr.jpg">
					  <p><a href="http://www.utexas.edu/" class="nav">The University of Texas at Austin</a> | <a href="http://www.utexas.edu/cons/" class="nav">

						College of Natural Sciences</a> | <a href="http://www.biosci.utexas.edu/" class="nav">School of Biological Sciences</a></p>
					</div>

				  </div>
