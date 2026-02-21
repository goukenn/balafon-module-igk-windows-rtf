<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfConvertCommand.php
// @date: 20260205 10:17:49
namespace igk\Windows\Rtf\System\Console\Commands;

use IGK\System\Console\AppExecCommand;
use IGK\System\IO\Path;
use igk\Windows\Rtf\Converter\MarkdownToRtf; 

/**
* 
* @package igk\Windows\Rtf\System\Console\Commands
* @author C.A.D. BONDJE DOUE
*/
class RtfConvertCommand extends AppExecCommand{
	var $command="--rtf:convert";
	var $desc="convert markdown file to rtf";
	var $category="windows-rtf";
	// var $options=[];
	var $usage='input_file.md output_file.rtf [options]';
	public function exec($command, ?string $input=null, ?string $output=null) { 
		$input || igk_die('missing input');
		$output = $output ?? Path::ChangeExtensionTo($input, '.rtf') ?? igk_die('missing output');
		$c = file_get_contents($input);
		$r = MarkdownToRtf::Convert($c);
		igk_io_w2file($output, $r);
	}
}