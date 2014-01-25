#!/bin/bash

export PATH=$PATH:/home/Mark/src/ttf2eot-0.0.2-2
outdir=../../london.hackspace.org.uk/fonts
mkdir -p $outdir

indir='OpenSans'
./createfonts.py "$indir/OpenSans-Regular.ttf" "$outdir/OpenSans-Regular" ttf woff
./createfonts.py "$indir/OpenSans-Bold.ttf" "$outdir/OpenSans-Bold" ttf woff

