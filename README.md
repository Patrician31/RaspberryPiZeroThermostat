# NOTE: these are my chicken scratch notes from putting this project together. You're welcome to leave a comment on the reddit page if you are stuck at any point and I will try to help you and while doing that also improve this readme file.

https://www.reddit.com/r/raspberry_pi/comments/58idhw/20_dollar_wifi_thermostat/

# RaspberryPiZeroThermostat

start with setting up the sensor by copying the code from:
https://learn.adafruit.com/dht-humidity-sensing-on-raspberry-pi-with-gdocs-logging/software-install-updated
and connecting the sensor as following:
connect pins 1 3.3V to pin 1 of DHT22 sensor
10k or 4.7k ohm pull up to pin2 of sensor, connect to IO pin 23 in raspberry pi
pin4 of sensor is ground, connect to pin9 of pi

#user pi pass raspberry

# move files to raspberry pi
scp -r <your directory>/. pi@<pi ip address>:/home/pi/thermostat/

# login:
ssh pi@<pi ip address>

# install stuff and set passwordd
sudo apt-get -y install apache2 mysql-server php5-mysql php5 libapache2-mod-php5 php5-mcrypt
# save your password
sudo mysql_install_db
sudo mysql_secure_installation

# move the php file
sudo mv index_sql.php /var/www/html/index.php

#mysql server setup
mysql -u root -p
SHOW DATABASES;
use thermostat
# current table for sensor readings
create table current (id int, temp double, humidity int);
insert into current (id, temp, humidity) values (1, 75.0, 60);
#select * from current;
# target table for saving control settings
# mode: 0 for idle, 1 for fan only, 2 for cooling and 3 for heating
create table target (id int, temp int, hist int, mode int);
insert into target (id, temp, hist, mode) values (1, 80, 1, 0);
#select * from target;


# automatically start script
sudo nano /etc/rc.local
#add this line at the bottom
sudo sh /home/pi/thermostat/startup.sh &

