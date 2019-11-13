<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\Compiler\Predef;


use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Project\Attribute\AttributeCollection;
use Skyline\Compiler\Project\Attribute\AttributeInterface;
use Skyline\Compiler\Project\Attribute\FilterConditionAttribute;
use Skyline\Compiler\Project\Attribute\HostAttribute;
use Skyline\Compiler\Project\ProjectInterface;
use Skyline\Kernel\Bootstrap;
use TASoft\Util\PathTool;

class SkylineEntryPointFileCompiler extends AbstractCompiler
{
    public function compile(CompilerContext $context)
    {
        $ROOT = $this->defineRoot($context->getProject(), $context);
        $FILTERS = $this->defineFilters($context->getProject());

        $params = [];
        if(($title = $context->getProject()->getAttribute(AttributeInterface::TITLE_ATTR_NAME)) && ($title = $title->getValue())) {
        } else {
            $title = "";
        }
        if(($desc = $context->getProject()->getAttribute(AttributeInterface::DESCRIPTION_ATTR_NAME)) && ($desc = $desc->getValue())) {
        } else {
            $desc = "";
        }

        if($title || $desc) {
            $params[] = '/** @var \\TASoft\\Service\\ServiceManager $SERVICES */';
            $params[] = 'global $SERVICES;';
            if($title) {
                $title = str_replace("'", "\\'", $title);
                $params[] = '$SERVICES->setParameter("AppTitle", "'.$title.'");';
            }
            if($desc) {
                $desc = str_replace("'", "\\'", $desc);
                $params[] = '$SERVICES->setParameter("AppDescription", "'.$desc.'");';
            }
        }

        $real =  implode("\n", $params);

        $skylineAppDir = PathTool::relative ($context->getProject()->getProjectRootDirectory().DIRECTORY_SEPARATOR, $context->getSkylineAppDataDirectory());

        $ctxParams = $context->getContextParameters();

        $BOOTSTRAP_CLASS = $ctxParams->getBootstrapClass() ?: Bootstrap::class;
        $APP_CLASS = $ctxParams->getApplicationClass() ?: "Skyline\Application\Application";


        $hosts = $context->getProject()->getAttribute(AttributeInterface::HOSTS_ATTR_NAME);
        $CORS = "";
        if($hosts instanceof AttributeCollection) {
            $accept = function($host, $remote = "", $cred = false, $label = "") use (&$CORS) {
                $args = [ var_export($host, true) ];
                if($remote || $cred || $label)
                    $args[] = var_export($remote, true);
                if($cred || $label)
                    $args[] = var_export($cred, true);
                if($label)
                    $args[] = var_export($label, true);

                $CORS .= sprintf("CORS::registerHost(".implode(", ", array_fill(0, count($args), "%s")).")", ...$args);
            };

            /** @var HostAttribute $attribute */
            foreach($hosts->getAttributes() as $attribute) {
                $host = $attribute->getName();
                $acceptsFrom = $attribute->getValue();

                if(is_array($acceptsFrom)) {
                    if($acceptsFrom) {
                        foreach ($acceptsFrom as $acc) {
                            $accept($host, $acc);
                        }
                    } else {
                        $CORS .= sprintf("CORS::registerHost(%s);\n", var_export($host, true));
                    }
                } else {
                    $accept($host, $acceptsFrom);
                }
            }
        }


        $DEBUG = var_export($context->isDevelopmentContext(), true);
        $TEST = var_export($context->isTestContext(), true);


        $content = <<< EOT
<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

use $APP_CLASS as Application;
use $BOOTSTRAP_CLASS as Bootstrap;
use Skyline\Kernel\Service\CORSService as CORS;

define("SKY_DEBUG", $DEBUG);
define("SKY_TEST", $TEST);

$FILTERS
$ROOT
require 'vendor/autoload.php';

\$configuration = Bootstrap::getConfigurationPath('$skylineAppDir');

$CORS

Bootstrap::bootstrap(\$configuration);

$real

\$app = new Application();
\$app->run();
EOT;
        $fn = $context->getProject()->getProjectPublicDirectory() . "/skyline.php";
        file_put_contents($fn, $content);
    }

    protected function defineRoot(ProjectInterface $proj, CompilerContext $context)
    {
        if($proj->getProjectRootDirectory() != $proj->getProjectPublicDirectory()) {
            if($context->useZeroLinks())
                return sprintf("chdir('%s');", $proj->getProjectRootDirectory());

            if($root = $proj->getAttribute("pub2root")) {
            } else {
                $pr = $proj->getProjectRootDirectory() . DIRECTORY_SEPARATOR . $proj->getProjectPublicDirectory() . DIRECTORY_SEPARATOR;
                $root = PathTool::relative( "$pr",$proj->getProjectRootDirectory().DIRECTORY_SEPARATOR);
            }

            return "chdir( dirname(__FILE__) . DIRECTORY_SEPARATOR . '$root');";
        }

        return "";
    }

    protected function defineFilters(ProjectInterface $project) {
        $FILTERS = "";

        if($filters = $project->getAttribute(AttributeInterface::FILTER_ATTR_NAME)) {
            /** @var AttributeCollection $filters */
            foreach($filters->getAttributes() as $filter) {
                $CONDS = [];
                /** @var FilterConditionAttribute $condition */
                foreach($filter->getConditions() as $condition) {
                    $right = "$";
                    $right .= sprintf("%s[\"%s\"]", $condition->getBank(), $condition->getName());

                    $left = var_export($condition->getValue(), true);
                    $op = "==";

                    if($condition->getModifier() & FilterConditionAttribute::NOT_MODIFIER)
                        $op = "!=";

                    $CONDS[] = "$right$op$left";
                }

                if($CONDS)
                    $CONDS = implode($filter->getConditionConcat(), $CONDS);
                else
                    $CONDS = "false";

                $action = $filter->getValue();
                $FILTERS .= "if($CONDS)\n\t$action;\n";
            }
        }
        return $FILTERS;
    }
}