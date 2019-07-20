# Raspberry Pi Receipt Printer Setup (USB)
Instructions for setting up a [Tiny Thermal Receipt Printer - TTL Serial / USB](https://www.adafruit.com/product/2751) on a Raspberry Pi. And how to program it with Python! 😊

## Materials Needed
- The Printer: [Tiny Thermal Receipt Printer - TTL Serial / USB](https://www.adafruit.com/product/2751)
- DC Power Supply: [5V 2A (2000mA) switching power supply - UL Listed](https://www.adafruit.com/product/276)
- DC Jack Adapter: [Female DC Power adapter - 2.1mm jack to screw terminal block](https://www.adafruit.com/product/368)
- Wire Strippers (or Scissors)
- Hex Screwdriver
- Raspberry Pi 3 or above (with network and power)

## Step 1: Prepare the Printer
1. Use a wire stripper (or scissors) to cut off the white head from one side of the red and black wires. (The wires should be included with the receipt printer). Plug the other end into the back of the receipt printer. 
2. Use a screwdriver to loosen the screws on the DC Jack Adapter. Then, strip the red and black wires and insert them into the jack adapter (as shown). Use the screwdriver to tighten again. ![power wires](https://cdn-learn.adafruit.com/assets/assets/000/001/944/large1024/components_poweradapt.jpg?1396777663)
3. Plug in the DC Power Supply to the DC Jack Adapter. The receipt printer should have power now. You can press the little button on the front of the receipt printer to test it.

## Step 2: Connecting with the Raspberry Pi and Installing Drivers
Instructions are from [here](https://learn.adafruit.com/instant-camera-using-raspberry-pi-and-thermal-printer/system-setup#install-software-2-7) under the "Install Software" section.
1. Connect the Micro USB cable to the back of the receipt printer, and connect the other end to a USB port on the Raspberry Pi.
2. Open a terminal in the Raspberry Pi (or SSH connection) and enter these commands:

    sudo apt-get update
    sudo apt-get install git cups wiringpi build-essential libcups2-dev libcupsimage2-dev python-serial python-pil python-unidecode

3. Use these commands to install the driver:

    cd ~
    git clone https://github.com/adafruit/zj-58
    cd zj-58
    make
    sudo ./install
4. Open up the receipt printer to reveal the test page. On that test page, check for a **baud rate**. We will need that number for our next step. Lost the test page?
![baud rate image](https://cdn-learn.adafruit.com/assets/assets/000/040/964/original/camera_raspberry_pi_components_test-baud.jpg)
5. Depending on the **baud rate**, enter one of the following commands:
	- If the **baud rate** is `192000`, enter this command in the terminal:
		- `sudo lpadmin -p ZJ-58  -E -v serial:/dev/ttyUSB0?baud=19200  -m zjiang/ZJ-58.ppd`
	- If the **baud rate** is `9600`, enter this command in the terminal:
		- `sudo lpadmin -p ZJ-58  -E -v serial:/dev/ttyUSB0?baud=9600  -m zjiang/ZJ-58.ppd`
6. Enter these two commands to finalize your changes and restart:

    sudo lpoptions -d ZJ-58
    sudo reboot

## Step 3: Download the Python Library and Start Programming

1. Download the library depending on your baud rate.
	- If the **baud rate** is 9600, download this file: https://github.com/bpmct/Python-Thermal-Printer-USB/archive/baud-9600.zip
	- If the **baud rate** is 19200, dowload this file: https://github.com/bpmct/Python-Thermal-Printer-USB/archive/baud-19200.zip
2. Unzip the file and navigate to the folder with your terminal.
3. Run `python printertest.py` to see if the printer will print.
4. Edit `printertest.py` to see how it works, and use that code to build your own programs that use the receipt printer. Make sure that `Adafruit_Thermal.py` stays in the same folder as your python programs.

## Additional Information:

Extra information and frequently asked questions.

### I lost my paper with the baud rate, or it wasn't included:
To re-print the test page, unplug the receipt printer from a power source and hold down the button on the receipt printer. Continue holding the button and plug the receipt printer in for two more seconds. A test page should begin printing.

### What does everything in `printertest.py` do?

The most important parts are the beginning and end. The rest is pretty self explanatory. It will print certain things out depending on what options are set. Run it, take a look at the test paper, and compare it to the code to see what it does.

#### First three lines:
`#!/usr/bin/python` < Declares that the script is written in Python

`from Adafruit_Thermal import *` < Imports the "Adafruit_Thermal" classes from the file **Adafruit_Thermal.py** that should be in the same folder.

`printer = Adafruit_Thermal("/dev/ttyUSB0", 9600, timeout=5)` < Assigns the **printer** variable to the receipt printer, which can be found at **/dev/ttyUSB0** and has a baud rate of **9600**.

#### Last three lines:
`printer.sleep()` < Tells printer to sleep

`printer.wake()` < Calls wake() before printing again, even if reset

`printer.setDefault()` < Restores printer to default settings (no bold, no italics, etc)

#### The text is blurry or faded:
Run `calibrate.py` (in the same folder as `printertest.py`) and follow these directions:

> Run `calibrate.py` before using the printer for the first time, any time
> a different power supply is used, or when using paper from a different
> source.
> 
> Prints a series of black bars with increasing "heat time" settings.
> Because printed sections have different "grip" characteristics than
> blank paper, as this progresses the paper will usually at some point
> jam -- either uniformly, making a short bar, or at one side or the
> other, making a wedge shape.  In some cases, the Pi may reset for lack
> of power.
> 
> Whatever the outcome, take the last number printed BEFORE any 
> distorted bar and enter in in Adafruit_Thermal.py as defaultHeatTime
> (around line 53).
> 
> You may need to pull on the paper as it reaches the jamming point,
> and/or just abort the program, press the feed button and take the last
> good number.