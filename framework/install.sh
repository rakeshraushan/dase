#!/bin/sh

echo "please set db username & password"
exit

cp -r inc/* ../inc
echo "copied in new local config"

rm -irf ../lib/Dase/Handlers/*
echo "deleted existing handlers"
cp -r handlers/* ../lib/Dase/Handlers
echo "copied in new handler"

rm -irf ../www/css/*
echo "deleted existing css"
cp -r css/* ../www/css
echo "copied in new css"

rm -irf ../www/js/*
echo "deleted existing js"
cp -r js/* ../www/js
echo "copied in new js"

rm -irf ../www/images/*
echo "deleted existing images"
cp -r images/* ../www/images
echo "copied in new images"

rm -irf ../templates/*
echo "deleted existing templates"
cp -r templates/* ../templates
echo "copied in new templates"

rm -irf ../lib/Dase/DBO/*
echo "deleted existing handlers"
cp -r lib/* ../lib/Dase/DBO
echo "copied in new handler"

php create_user_table.php
php class_gen.php
