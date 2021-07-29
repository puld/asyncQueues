<?php

/**
 * Description of Log.php
 *
 * @author puld
 */
class Log
{
	protected $fileName;

	public function __construct(string $fileName)
	{
		$this->fileName = $fileName;

		if (file_exists($this->fileName))
		{
			unlink($this->fileName);
		}
	}

	public function push(string $str)
	{
		file_put_contents($this->fileName, $str . "\n", FILE_APPEND | LOCK_EX);
	}
}