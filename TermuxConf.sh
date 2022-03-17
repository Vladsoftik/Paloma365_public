#!/bin/bash
TEXT_RESET='\e[0m'
TEXT_YELLOW='\e[0;33m'
TEXT_GREEN='\e[0;32m'
TEXT_RED_B='\e[1;31m'

#widget
mkdir -p $HOME/.shortcuts
echo "mysqld_safe -u root &" > $HOME/.shortcuts/StartPalomaServer

#update
echo -e $TEXT_GREEN
echo 'APT update'
echo -e $TEXT_RESET
apt update
echo -e $TEXT_GREEN
echo 'APT upgrade'
echo -e $TEXT_RESET
apt-get -o DPkg::Options::="--force-confnew" -y upgrade

#install maria
echo -e $TEXT_GREEN
echo 'install mariadb'
echo -e $TEXT_RESET
pkg install mariadb -y

#start mariadb
echo -e $TEXT_GREEN
echo 'start service'
echo -e $TEXT_RESET
mysqld_safe -u root &
sleep 2

#new user
mysql -u $(whoami) -e "use mysql; DROP USER 'root'@'localhost'; CREATE USER 'root'@'%' IDENTIFIED BY 'root'; flush privileges;"

termux-wake-lock

echo -e $TEXT_GREEN
echo 'Сomplete'
echo -e $TEXT_RESET