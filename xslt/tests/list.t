#!/bin/bash

xsltproc --stringparam app_root http://quickdraw.laits.uexas.edu/dasetest/ --stringparam src http://quickdraw.laits.utexas.edu/dasetest/atom   --stringparam msg ooo ../collection/list.xsl ../site/layout.xml
