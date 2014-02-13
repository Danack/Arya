<?php

namespace Arya\Routing;

class CompositeRegexRouter implements Router {

    private $staticRoutes = [];
    private $variableRoutes = [];
    private $expressionToOffsetMap = [];
    private $currentOffset = 1; // skip offset 0, which is the full match
    private $compositeRegex = '';

    private static $E_DUPLICATE_PARAMETER_CODE = 1;
    private static $E_REQUIRES_IDENTIFIER_CODE = 2;
    private static $E_DUPLICATE_PARAMETER_STR = 'Route cannot use the same variable "%s" twice in the same rule';
    private static $E_REQUIRES_IDENTIFIER_STR = '%s must be followed by at least 1 character';

    /**
     * Add a handler for the specified HTTP method verb and request URI route
     *
     * @param string $httpMethod
     * @param string $route
     * @param callable $handler
     * @throws \InvalidArgumentException
     * @return void
     */
    public function addRoute($httpMethod, $route, $handler) {
        $route = '/' . ltrim($route, '/');
        if ($this->containsVariable($route)) {
            $this->addVariableRoute($httpMethod, $route, $handler);
        } else {
            $this->staticRoutes[$route][$httpMethod] = $handler;
        }
    }

    /**
     * Match the specified HTTP method verb and URI against added routes
     *
     * @param string $httpMethod
     * @param string $uri
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     * @return array
     */
    public function route($httpMethod, $uri) {
        if (isset($this->staticRoutes[$uri])) {
            return $this->matchStaticRoute($httpMethod, $uri);
        } elseif ($this->compositeRegex) {
            return $this->doCompositeRegexMatch($httpMethod, $uri);
        } else {
            throw new NotFoundException;
        }
    }

    private function matchStaticRoute($httpMethod, $uri) {
        if (isset($this->staticRoutes[$uri][$httpMethod])) {
            return [$this->staticRoutes[$uri][$httpMethod], []];
        } else {
            throw new MethodNotAllowedException(array_keys($this->staticRoutes[$uri]));
        }
    }

    private function doCompositeRegexMatch($httpMethod, $uri) {
        if (!preg_match($this->compositeRegex, $uri, $matches)) {
            throw new NotFoundException;
        }

        // find first non-empty match
        for ($i = 1; '' === $matches[$i]; ++$i);

        $rules = $this->variableRoutes[$i];
        if (!isset($rules[$httpMethod])) {
            throw new MethodNotAllowedException(array_keys($rules));
        }

        $rule = $rules[$httpMethod];
        $vars = [];
        foreach ($rule->variables as $var) {
            $vars[$var] = $matches[$i++];
        }

        return [$rule->handler, $vars];
    }

    private function escapeUriForRule($string) {
        $expression = str_replace(
            '\$', '$', preg_quote($string, '~')
        );
        
        return str_replace('\\\\$', '\\$', $expression);
    }

    private function containsVariable($escapedUri) {
        $varPos = 0;
        
        while(($varPos = strpos($escapedUri, '$', $varPos + 1)) !== FALSE) {
            if($escapedUri[$varPos-1] !== '\\') {
                return true;
            }
        }
        
        return false;
    }

    private function buildExpressionForRule(Rule $rule) {
        return preg_replace_callback('~(?<!\\\\)(\$#?)([^/]*)~', function ($matches) use ($rule) {
            list(, $prefix, $var) = $matches;

            if (strlen($var) < 1) {
                throw new \InvalidArgumentException(
                    sprintf(self::$E_REQUIRES_IDENTIFIER_STR, $prefix),
                    self::$E_REQUIRES_IDENTIFIER_CODE
                );
            }

            if (isset($rule->variables[$var])) {
                throw new \InvalidArgumentException(
                    sprintf(self::$E_DUPLICATE_PARAMETER_STR, $var),
                    self::$E_DUPLICATE_PARAMETER_CODE
                );
            }

            $rule->variables[$var] = $var;

            $def = $prefix === '$#' ? '\d+' : '[^/]+';
            return "($def)";
        }, $this->escapeUriForRule($rule->route));
    }

    private function addVariableRoute($httpMethod, $route, $handler) {
        $rule = new Rule;

        $rule->httpMethod = $httpMethod;
        $rule->route = $route;
        $rule->handler = $handler;
        $rule->expression = $this->buildExpressionForRule($rule);

        $offset =& $this->expressionToOffsetMap[$rule->expression];
        if (null === $offset) {
            $offset = $this->currentOffset;
            $this->currentOffset += count($rule->variables);
        }

        $this->variableRoutes[$offset][$httpMethod] = $rule;
        $this->buildCompositeRegex();
    }

    private function buildCompositeRegex() {
        $expressions = array_keys($this->expressionToOffsetMap);
        $this->compositeRegex = '~^(?:' . implode('|', $expressions) . ')$~';
    }

}
