<?php
/**
 * More command line interface cases (complements cli_test.php): single digits,
 * per segment colors and the byte/word encoding of the color values, checked
 * against the I2C protocol of the display firmware (scoreboard-atmega8-i2c):
 *
 *   devices:  0x10 (16) away runs, 0x11 (17) home runs, 0x12 (18) inning, 0x13 (19) B/S/O
 *   commands: 0x00/0x02 red+blue segment 0/1 (word: low byte red, high byte blue)
 *             0x01/0x03 green segment 0/1 (9 bit, low byte first)
 *             0x07/0x08 ASCII symbol on ones/tens digit
 *
 * @license see License.md
 */
class cli_protocol_test extends PHPUnit_Framework_TestCase
{
	use known_bug_trait;

	public function test_single_digits_send_ascii_code()
	{
		$this->assertCli(array('RH1', '7'),  array('-y 1 17 7 55'));
		$this->assertCli(array('RH10', '2'), array('-y 1 17 8 50'));
		$this->assertCli(array('RG1', 'A'),  array('-y 1 16 7 65'));
		$this->assertCli(array('RG10', '-'), array('-y 1 16 8 45'));
		$this->assertCli(array('I1', '9'),   array('-y 1 18 7 57'));
	}

	public function test_short_digit_names()
	{
		// H1, H10, G1, G10 are accepted by the command parser, but have no I2C
		// address in $i2c_addr (only RH1, RH10, RG1, RG10 have) -> exit 2.
		$this->known_bug('H1/H10/G1/G10 have no i2c address', function () {
			$this->assertCli(array('H1', '7'), array('-y 1 17 7 55'));
			$this->assertCli(array('G10', '1'), array('-y 1 16 8 49'));
		});
	}

	public function test_inning_tens_is_limited_to_one()
	{
		$this->assertCli(array('I10', '1'), array('-y 1 18 8 49'));
		$this->assertCli(array('I10', '0'), array('-y 1 18 8 48'));
		// Values > 1 are replaced by the integer 1, and write_letter() then takes
		// $letter[0] of an int (null) -> sends 0x00 instead of '1'.
		$this->known_bug('I10 with value > 1 sends 0x00', function () {
			$this->assertCli(array('I10', '5'), array('-y 1 18 8 49'));
		});
	}

	public function test_color_red_blue_word_order()
	{
		// RRGGBB = 123456 -> word 0xBBRR, i2cset sends the low byte (red) first
		$this->assertCli(array('color', 'RH', '123456'), array(
			'-y 1 17 0 0x5612 w', '-y 1 17 2 0x5612 w',
			'-y 1 17 1 0x68', '-y 1 17 3 0x68',
		));
	}

	public function test_color_green_is_9_bit()
	{
		// Green is doubled (0..510): values > 0xFF need a word write, smaller
		// ones are sent as a single byte (the firmware zero-fills the rest).
		$this->assertCli(array('color', 'RG', '00ff00'), array(
			'-y 1 16 0 0x0000 w', '-y 1 16 2 0x0000 w',
			'-y 1 16 1 0x1fe w', '-y 1 16 3 0x1fe w',
		));
		$this->assertCli(array('color', 'RG', '007f00'), array(
			'-y 1 16 0 0x0000 w', '-y 1 16 2 0x0000 w',
			'-y 1 16 1 0xfe', '-y 1 16 3 0xfe',
		));
	}

	public function test_color_single_segment_targets()
	{
		// Inning ones / strikes / outs are wired to segment 0, balls to segment 1
		$this->assertCli(array('color', 'I', '010203'), array('-y 1 18 0 0x0301 w', '-y 1 18 1 0x4'));
		$this->assertCli(array('color', 'S', '010203'), array('-y 1 19 0 0x0301 w', '-y 1 19 1 0x4'));
		$this->assertCli(array('color', 'B', '010203'), array('-y 1 19 2 0x0301 w', '-y 1 19 3 0x4'));
	}
}
