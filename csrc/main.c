/**
 * Main Program to shutdown the raspberry pi. This process needs to be run as root to allow this.
 * All this program does is to run the shutdown command. If the process has the setuid bit enabled and belongs to root this should work.
 */

#include <stdio.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>

int main()
{
	/* Disable output */
	system("i2cset -y 1 0x10 0x07 0x20");
	system("i2cset -y 1 0x10 0x08 0x20");
	
	system("i2cset -y 1 0x11 0x07 0x20");
	system("i2cset -y 1 0x11 0x08 0x20");

	system("i2cset -y 1 0x12 0x07 0x20");
	system("i2cset -y 1 0x12 0x08 0x20");

	system("i2cset -y 1 0x13 0x07 0x20");
	system("i2cset -y 1 0x13 0x08 0x20");

	/* Shutdown */
	system("shutdown -h -P now");
}

