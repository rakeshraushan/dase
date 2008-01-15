#!/bin/bash

xsltproc menu_css.xsl palette.xml > menu.css 
echo 'created menu' 

xsltproc admin_menu_css.xsl palette.xml > admin_menu.css 
echo 'created admin menu' 


