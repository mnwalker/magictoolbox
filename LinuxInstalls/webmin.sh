#!/usr/bin/env bash

# /* Created by Mark Walker 2016
# * Run using command "sudo webmin.sh"
# * For debian/ubuntu based distros only
# * Licensed under MIT
# */
# wget https://raw.githubusercontent.com/mnwalker/magictoolbox/master/LinuxInstalls/webmin.sh && chmod +x webmin.sh && ./webmin.sh

echo "deb http://download.webmin.com/download/repository sarge contrib" >> /etc/apt/sources.list

cd /root
wget http://www.webmin.com/jcameron-key.asc
apt-key add jcameron-key.asc
rm jcameron-key.asc

apt-get update
apt-get install -y webmin
