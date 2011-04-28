#!/bin/sh

echo "please set db username & password"
echo "please set RewriteBase in .htaccess"
exit

#target relative to source!
ln -s lib/Dase/Handler ../handlers

chgrp -R apache ../files/*
chmod -R g+w ../files/*

cp -r inc/* ../inc
echo "copied in new local config"

rm -rf ../lib/Dase/Handler/*
echo "deleted existing handlers"
cp -r handlers/* ../lib/Dase/Handler
echo "copied in new handler"

rm -rf ../www/css/*
echo "deleted existing css"
cp -r css/* ../www/css
echo "copied in new css"

rm -rf ../www/js/*
echo "deleted existing js"
cp -r js/* ../www/js
echo "copied in new js"

rm -rf ../www/images/*
echo "deleted existing images"
cp -r images/* ../www/images
echo "copied in new images"

rm -rf ../templates/*
echo "deleted existing templates"
cp -r templates/* ../templates
echo "copied in new templates"

rm -rf ../lib/Dase/DBO/*
echo "deleted existing handlers"
cp -r lib/* ../lib/Dase/DBO
echo "copied in new handler"

php create_user_table.php
php class_gen.php
