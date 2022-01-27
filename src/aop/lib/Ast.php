<?php


namespace iflow\aop\lib;


use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;

class Ast
{

    private Parser $astParser;
    private PrettyPrinterAbstract $printer;

    public function __construct() {
        $parserFactory = new ParserFactory();
        $this->astParser = $parserFactory -> create(ParserFactory::ONLY_PHP7);
        $this->printer = new Standard();
    }

    public function proxy(string $class, ?NodeVisitorAbstract $node = null, string $aspectClass = ""): string
    {
        $code = $this->getCode($class);

        if ($code === "") return $code;

        // 解析后的 ast 树
        $stmts = $this->parse($code);
        $traverser = new NodeTraverser();
        $node = $node ?: new nodeVisitor($class, $aspectClass);

        // 加入 自定义 ast 节点
        $traverser -> addVisitor($node);
        return $this->printer->prettyPrintFile($traverser->traverse($stmts));
    }

    /**
     * 将代码解析为 ast
     * @param string $code
     * @return array|null
     */
    public function parse(string $code): ?array
    {
        return $this->astParser -> parse($code);
    }

    /**
     * 获取指定类 code
     * @param string $class
     * @return string
     */
    private function getCode(string $class): string {
        $root = explode("\\", $class)[0] === "iflow" ?
            app() -> getFrameWorkPath() : app() -> getDefaultRootPath();

        $filePath = str_replace("\\", "/", $root . $class . ".php");
        return file_exists($filePath) ? file_get_contents($filePath) : "";
    }
}