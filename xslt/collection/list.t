#!/bin/bash

xsltproc --stringparam app_root http://quickdraw.laits.uexas.edu/dasetest/ --stringparam collections http://quickdraw.laits.utexas.edu/dasetest/xml  --stringparam local-layout ../collection/list.xml ../collection/list.xsl ../site/layout.xml
