#!/usr/bin/php
<?php
// Software for testing the i2c connection and numbers and pwm on the board
// Uses the default port 0x10 (RH)
// Author: Martin Beckmann
// License: See License.md
usleep(2000000);
system("i2cset -y 1 0x10 0x00 0xFFFF w");
system("i2cset -y 1 0x10 0x01 0xFFFF w");

# Numbers Test
system("i2cset -y 1 0x10 0x07 0");
system("i2cset -y 1 0x10 0x08 0");
usleep(500000);
system("i2cset -y 1 0x10 0x06 0");
usleep(500000);
system("i2cset -y 1 0x10 0x06 11");
usleep(500000);
system("i2cset -y 1 0x10 0x06 22");
usleep(500000);
system("i2cset -y 1 0x10 0x06 33");
usleep(500000);
system("i2cset -y 1 0x10 0x06 44");
usleep(500000);
system("i2cset -y 1 0x10 0x06 55");
usleep(500000);
system("i2cset -y 1 0x10 0x06 66");
usleep(500000);
system("i2cset -y 1 0x10 0x06 77");
usleep(500000);
system("i2cset -y 1 0x10 0x06 88");
usleep(500000);
system("i2cset -y 1 0x10 0x06 99");
usleep(500000);

# PWM
system("i2cset -y 1 0x10 0x07 8");
system("i2cset -y 1 0x10 0x08 8");
# PWM Port 1
system("i2cset -y 1 0x10 0x02 0x0000 w");
system("i2cset -y 1 0x10 0x03 0x0000 w");
system("i2cset -y 1 0x10 0x00 0x0000 w");
system("i2cset -y 1 0x10 0x01 0x0000 w");
for ($i = 0; $i < 256; $i++)
{
	$ih = dechex($i);
	system("i2cset -y 1 0x10 0x00 0x00$ih w");
	usleep(10000);
}
usleep(100000);
system("i2cset -y 1 0x10 0x00 0x0000 w");
system("i2cset -y 1 0x10 0x01 0x0000 w");

for ($i = 0; $i < 256; $i++)
{
	$ih = dechex($i);
	system("i2cset -y 1 0x10 0x00 0x{$ih}00 w");
	usleep(10000);
}

usleep(100000);
system("i2cset -y 1 0x10 0x00 0x0000 w");
system("i2cset -y 1 0x10 0x01 0x0000 w");

for ($i = 0; $i < 512; $i++)
{
	$ih = dechex($i);
	system("i2cset -y 1 0x10 0x01 0x$ih w");
	usleep(10000);
}

# PWM 2
system("i2cset -y 1 0x10 0x00 0x0000 w");
system("i2cset -y 1 0x10 0x00 0x0000 w");
system("i2cset -y 1 0x10 0x02 0x0000 w");
system("i2cset -y 1 0x10 0x03 0x0000 w");
for ($i = 0; $i < 256; $i++)
{
	$ih = dechex($i);
	system("i2cset -y 1 0x10 0x02 0x00$ih w");
	usleep(10000);
}
usleep(100000);
system("i2cset -y 1 0x10 0x02 0x0000 w");
system("i2cset -y 1 0x10 0x03 0x0000 w");

for ($i = 0; $i < 256; $i++)
{
	$ih = dechex($i);
	system("i2cset -y 1 0x10 0x02 0x{$ih}00 w");
	usleep(10000);
}

usleep(100000);
system("i2cset -y 1 0x10 0x02 0x0000 w");
system("i2cset -y 1 0x10 0x03 0x0000 w");

for ($i = 0; $i < 512; $i++)
{
	$ih = dechex($i);
	system("i2cset -y 1 0x10 0x03 0x$ih w");
	usleep(10000);
}
