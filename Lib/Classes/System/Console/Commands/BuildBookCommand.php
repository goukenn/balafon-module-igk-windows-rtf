<?php
// @author: C.A.D. BONDJE DOUE
// @file: BuildBookCommand.php
// @date: 20260210 07:54:25
namespace igk\Windows\Rtf\System\Console\Commands;

use IGK\Helper\Activator;
use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use igk\Windows\Rtf\Builder\IRtfBookBuilderOptions;
use igk\Windows\Rtf\Builder\RtfBookBuilder;

/**
* 
* @package igk\Windows\Rtf\System\Console\Commands
* @author C.A.D. BONDJE DOUE
*/
class BuildBookCommand extends AppExecCommand{
	var $command="--rtf:build-book";
	var $desc="create a .rtf book script";
	var $category="windows-rtf ";	
	var $options=[
		"--title"=>"define book's title",
		'--with-page-number'=>'flag: support page number'

	];
	var $usage = "location_dir_or_file outputfile [options]";
	/**
	 * 
	 * @param mixed $command 
	 * @param null|string $location_dir 
	 * @param null|string $outputfile 
	 * @return int|null 
	 */
	public function exec($command, ?string $location_dir=null, ?string $outputfile=null) { 

		$title = igk_getv($command->options, '--title', 'BalafonGeneratedBook');
		$options = Activator::CreateNewInstance(IRtfBookBuilderOptions::class , [
			'withPage'=>property_exists($command->options, '--with-page-number')
		]);
		Logger::info('build book - .... please wait ...');
		if (RtfBookBuilder::Build($location_dir, $outputfile, $title, $options)){
			Logger::success('book builded');
		}else{
			Logger::danger('book generation failed');
			return -1;
		}
	}
}