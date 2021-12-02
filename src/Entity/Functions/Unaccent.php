<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "unaccent" "(" Column ")"
 * @link www.doctrine-project.org
 */
final class Unaccent extends FunctionNode
{
	/** @var Node */
	public $column;


	/**
	 * @param  Parser  $parser
	 * @return void
	 */
	public function parse(Parser $parser): void
	{
		$parser->match(Lexer::T_IDENTIFIER); // (2)
		$parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)

		$this->column = $parser->StringPrimary(); // (4)

		$parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
	}


	/**
	 * @param  SqlWalker  $sqlWalker
	 * @return string
	 */
	public function getSql(SqlWalker $sqlWalker): string
	{
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);

		return 'UNACCENT('.$column.')';
	}
}
