<?php

namespace App\Currency\Services;

class CalculatePriceService
{

    /**
     * Calculate the price with the desired formula
     *
     * @param string $formula
     * @param $p => Price
     * @param $ex => Exchange Rate
     * @param $et => Transfer Rate Fee
     * @param $un => Unforeseen expenses
     * @param $pr => Profit rate
     * @return
     */
	public function calculation(string $formula, $price, $ex, $transferRateFee = '', $unforeseenExpenses = '', $profitRate = '')
	{
		$et = $transferRateFee ? +$this->proccess($transferRateFee, $price, $ex, '', '', '') : '';
		$un = $unforeseenExpenses ? +$this->proccess($unforeseenExpenses, $price, $ex, $et, '', '') : '';
		$pr = $profitRate ? round(+$this->proccess($profitRate, $price, $ex,  $et, $un, '')) : '';
		return $this->proccess($formula, $price, $ex,  $et, $un, $pr);
	}

	public function proccess($formula, $price, $ex, $et = '', $un = '', $pr = '')
	{	
		$evaluator = new \Matex\Evaluator();

		$replacedFormula = str_replace(
			['++', '--', '**', '//'], ['+', '-', '*', '/'], 
			str_replace(
				['PR', 'P', 'EX', 'ET', 'UN'], 
				[$pr, $price, $ex, $et, $un], 
				$formula
			)
		);
		$form = $replacedFormula;

		preg_match_all('/\(([^)]+)\)/', $replacedFormula, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
		    $parenthesesContent = $match[1];
		    $form = str_replace("($parenthesesContent)", $evaluator->execute($parenthesesContent), $form);
		}
		return $evaluator->execute($form);
	}

	public function replace($string, $vars)
	{
		return str_replace(['++', '--', '**', '//'], ['+', '-', '*', '/'], str_replace(['P', 'EX', 'ET', 'UN', 'PR'], $vars, $string));
	}
}