#!/bin/bash

before=""
after=".list.netocentre.fr"

for line in $(cat $1);
do
  /home/sympa/scripts/createRobot.pl "${before}${line}${after}"
done 
