<?php


namespace iflow\aop\lib;


use PhpParser\Comment\Doc;
use PhpParser\NodeAbstract;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

class nodeVisitor extends NodeVisitorAbstract
{

    public function __construct(
        // 需要重写的类
        protected string $className,
        // 切面类
        protected string $aspectClass
    ) {}

    public function getProxyClassName(): string
    {
        return '_' . sha1($this->aspectClass . $this->className);
    }

    public function getAspectClassName(): string
    {
        return basename(str_replace('\\', '/', $this->aspectClass));
    }

    public function leaveNode(Node $node): NodeAbstract|null
    {
        // 生成临时类
        if ($node instanceof Class_ || $node instanceof Node\Stmt\Trait_) {
            // 返回类
            $params = [
                'flags' => $node->flags
            ];

            if ($node instanceof Class_) {
                if ($node->extends) $params['extends'] = $node ->extends;
            } else {
                array_unshift($node -> stmts,  new Node\Stmt\TraitUse([new Name('\\' . $this->className)]));
            }

            $params['stmts'] = $node->stmts;
            $params['attrGroups'] = $node->attrGroups;
            return $this->setDocComment(
                new Class_($this->getProxyClassName(), $params), $node -> getDocComment()
            );
        }

        // 重写方法
        if ($node instanceof ClassMethod && !$node->isStatic() && ($node->isPublic() || $node->isProtected())) {
            // 获取调用方法
            $methodName = $node->name->toString();

            // 验证方法 前两个字符是否为 下划线
            if (substr($methodName, 0, 2) === '__') return null;

            $uses = [];
            // 方法参数
            foreach ($node->params as $key => $param) {
                if ($param instanceof Param) {
                    $uses[$key] = new Param($param->var, null, null, true);
                }
            }

            // 重写方法 方法
            $params = [
                new Closure([
                    'static' => $node->isStatic(),
                    'uses' => $uses,
                    'stmts' => $node->stmts,
                    'attrGroups' => $node->attrGroups
                ]),
                new FuncCall(new Name('func_get_args'))
            ];
            $stmts = [
                new Node\Stmt\Expression(
                    new Node\Expr\Assign(new Variable($this->getAspectClassName()),
                        new Node\Expr\New_(
                            new Name(str_replace("/", "\\", "\\". trim($this->aspectClass, "\\")))
                        )
                    )
                ),
                new Return_(new MethodCall(new Variable($this->getAspectClassName()), 'process', $params))
            ];
            $returnType = $node->getReturnType();
            if ($returnType instanceof Name && $returnType->toString() === 'self') {
                $returnType = new Name('\\' . $this->className);
            }

            return $this->setDocComment(new ClassMethod($methodName, [
                'flags' => $node->flags,
                'byRef' => $node->byRef,
                'params' => $node->params,
                'returnType' => $returnType,
                'stmts' => $stmts,
                'attrGroups' => $node->attrGroups
            ]), $node -> getDocComment());
        }
        return null;
    }


    public function setDocComment(NodeAbstract $node, ?Doc $doc): NodeAbstract {
        if ($doc === null) {
            return $node;
        }
        $node -> setDocComment($doc);
        return $node;
    }
}