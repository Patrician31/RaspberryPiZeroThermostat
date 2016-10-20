#!/usr/bin/python

# Import required Python libraries
import RPi.GPIO as GPIO
import time
import MySQLdb
import Adafruit_DHT

# setup temp sensor
sensor = Adafruit_DHT.DHT22
sensor_pin = 23

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

wait_interval = 15
second_to_minute = 60
cooloff_minutes = 5

# init list with pin numbers

green_w = 17
yellow_w = 27
blue_w = 22
purple_w = 24

fan_green = green_w
heater_white = blue_w
cool_yellow = yellow_w

# loop through pins and set mode and state to 'low'
pins = [green_w, yellow_w, blue_w, purple_w]
for pin in pins:
  GPIO.setup(pin, GPIO.OUT)
  GPIO.output(pin, GPIO.HIGH)

time.sleep(5)

# all the funcntions
def cleanup ():
  print "cleanup function"
  for pin in pins:
    GPIO.output(pin, GPIO.HIGH)
  return

def fan_only ():
  print "fan only"
  GPIO.output(fan_green, GPIO.LOW)
  return

def cooling ():
  print "cooling"
  GPIO.output(fan_green, GPIO.LOW)
  GPIO.output(cool_yellow, GPIO.LOW)
  return

def heating ():
  print "heating"
  GPIO.output(fan_green, GPIO.LOW)
  GPIO.output(heater_white, GPIO.LOW)
  return

# initial values
temp_current = 75
humidity = 40
temp_target = 75
hysteresis = 5
cooloff_wait = True
cooloff_start = True
cooloff_timer = 0
mode_new = 0
mode = 0

# main control loop
while True:
  # wait the loop wait interval
  time.sleep(wait_interval)
  # read temp from sensor
  rdhumidity, rdtemp_c = Adafruit_DHT.read_retry(sensor, sensor_pin)
  # convert temp to Fahrenheit
  rdtemp = 9.0/5.0 * rdtemp_c + 32
  # read values from sql database
  db = MySQLdb.connect("localhost","root",<yourPassword>,"thermostat" )
  cursor = db.cursor()
  # Prepare SQL query to UPDATE required records
  sql = "UPDATE current SET temp=" + str(rdtemp) + ", humidity=" + str(rdhumidity) + " WHERE id=1"
  #sql_current = "SELECT * FROM current WHERE id=1"
  sql_target = "SELECT * FROM target WHERE id=1"
  try:
    # Execute the SQL command
    # write temps first
    cursor.execute(sql)
    db.commit()
    #cursor.execute(sql_current)
    #results_current = cursor.fetchall()
    #for row in results_current:
    #  temp_current = row[1]
    #  humidity = row[2]
    cursor.execute(sql_target)
    results_target = cursor.fetchall()
    for row in results_target:
      temp_target = row[1]
      hysteresis = row[2]
      mode_new = row[3]
  except MySQLdb.Error, e:
    print "MySQL Error: %s" % str(e)
  #disconnect from db
  db.close()
  #control loop starts here
  print "temp_current " + str(rdtemp) + " humidity " + str(rdhumidity) + " temp_target " + str(temp_target) + " hysteresis " + str(hysteresis)
  # mode: 0 for idle, 1 for fan only, 2 for cooling and 3 for heating
  # when chainging modes start the cooloff timer except for when going from idle to something else
  if mode_new!=mode and mode!=0:
    cooloff_start = True
  # update mode to newly written mode (resaves if not changed)
  mode = mode_new
  # wait five minutes after turning off the system before starting up again
  if cooloff_start:
    print "cooloff timer wait of " + str(second_to_minute*cooloff_minutes/wait_interval) + " current wait " + str(cooloff_timer)
    if (cooloff_timer >= (second_to_minute*cooloff_minutes/wait_interval)):
      cooloff_wait = False
      cooloff_start = False
    else:
      cooloff_timer = cooloff_timer+1
      cooloff_wait = True
  else:
    print "clear cooloff timer"
    cooloff_timer = 0
    cooloff_wait = False
  # actual state changes
  if mode==0 or cooloff_wait:
    cleanup()
  elif mode==1:
    fan_only()
  elif mode==2:
    if temp_target <= rdtemp:
      cooling()
    elif temp_target-hysteresis > rdtemp:
      cooloff_start = True # cooloff after reaching desired temp
      cleanup()
  elif mode==3:
    if temp_target >= rdtemp:
      heating()
    elif temp_target+hysteresis < rdtemp:
      cooloff_start = True # cooloff after reaching desired temp
      cleanup()
  else:
    print "error mode " + str(mode)

