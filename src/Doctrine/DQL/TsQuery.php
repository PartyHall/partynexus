<?php

namespace App\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class TsQuery extends FunctionNode
{
    private Node $field;
    private Node $query;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            '%s @@ (plainto_tsquery(\'simple\', %s) || to_tsquery(\'simple\', %s || \':*\'))',
            $this->field->dispatch($sqlWalker),
            $this->query->dispatch($sqlWalker),
            $this->query->dispatch($sqlWalker),
        );
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->field = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->query = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
