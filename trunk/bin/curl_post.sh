#!/bin/bash

TITLE="M Test"


/usr/bin/curl --data-binary @test.jpg -X post -H 'Content-type: image/jpeg' -H "Slug: $TITLE" -u pkeane:skeletonkey http://quickdraw.laits.utexas.edu/dase1/media/keanepj?auth=http
