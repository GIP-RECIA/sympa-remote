#!/bin/bash

before=""
after=".list.touraine-eschool.fr."

for line in $(cat $1);
do
	perl deleteRobot.pl "${before}${line}${after}";
done 
