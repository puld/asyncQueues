<?php

/**
 * Класс очередей предназанчен для накопления элементов и запуска обработки элементов в несколько очередей асинхронно.
 *
 * @author puld
 */
class Queues
{
	/**
	 * Элементы очереди
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * @param mixed $item
	 */
	public function addItem($item)
	{
		$this->items[] = $item;
	}

	/**
	 * Запуск обработки накопленных элементов.
	 * Форкается указанное количество дочерних процессов, каждый из которых обрабатывает элемент кратный своему номеру.
	 * Например, $procAmount=3
	 * Элементы    Номер очереди
	 *    1            1
	 *    2            2
	 *    3            3
	 *    4            1
	 *    5            2
	 *    6            3
	 *    7            1
	 *    ...            ...
	 *
	 * @param int $procAmount
	 * @param callable $handler
	 */
	public function run(int $procAmount, callable $handler)
	{
		echo "Running \n";

		// организация дочерних процессов
		for ($procNum = 0; $procNum < $procAmount; $procNum++)
		{
			$pid = pcntl_fork();
			if ($pid < 0)
			{
				fwrite(STDERR, "Cannot fork $procNum\n");
				exit(1);
			}
			else if ($pid == 0)
			{
				// Дочерний процесс должен выйти из цикла
				break;
			}
			else
			{
				// Родительский процесс
				echo "fork $procNum\n";
			}
		}

		if ($pid)
		{
			// ожидание завершения всех дочерних процессов
			for ($i = 0; $i < $procAmount; $i++)
			{
				pcntl_wait($status);
				$exitcode = pcntl_wexitstatus($status);
				if ($exitcode)
				{
					echo "Код ошибки процесса $i\n";
				}
			}
		}
		else
		{
			// Дочерний процесс
			$l = count($this->items);
			for ($i = $procNum; $i < $l; $i += $procAmount)
			{
				try
				{
					$handler($this->items[$i]);
				}
				catch (Throwable $e)
				{
					echo "Ошибка обработки элемента $i\n";
				}
			}
			exit(0);
		}
	}
}