/**
 * Main Program to shutdown the raspberry pi. This process needs to be run as root to allow this.
 * All this program does is to run the shutdown command. If the process has the setuid bit enabled and belongs to root this should work.
 */

#include <stdio.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>

/* Use absolute paths: this program runs with root privileges (setuid), so PATH must not be trusted. */
#define I2CSET "/usr/sbin/i2cset"
#define SHUTDOWN "/sbin/shutdown"

int main(void)
{
	/* Disable output */
	system(I2CSET " -y 1 0x10 0x07 0x20");
	system(I2CSET " -y 1 0x10 0x08 0x20");
	
	system(I2CSET " -y 1 0x11 0x07 0x20");
	system(I2CSET " -y 1 0x11 0x08 0x20");

	system(I2CSET " -y 1 0x12 0x07 0x20");
	system(I2CSET " -y 1 0x12 0x08 0x20");

	system(I2CSET " -y 1 0x13 0x07 0x20");
	system(I2CSET " -y 1 0x13 0x08 0x20");

	/* Shutdown */
	system(SHUTDOWN " -h -P now");

	return 0;
}

