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
use Skyline\Compiler\Project\ProjectInterface;
use Skyline\Kernel\Bootstrap;

class SkylineEntryPointFileCompiler extends AbstractCompiler
{
    public function compile(CompilerContext $context)
    {
        $ROOT = $this->defineRoot($context->getProject());
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

        $skylineAppDir = SkyRelativePath($context->getProject()->getProjectRootDirectory()."/_", $context->getSkylineAppDataDirectory());

        $ctxParams = $context->getContextParameters();

        $BOOTSTRAP_CLASS = $ctxParams->getBootstrapClass();
        $APP_CLASS = $ctxParams->getApplicationClass();


        $hosts = $context->getProject()->getAttribute(AttributeInterface::HOSTS_ATTR_NAME);
        $CORS = "";
        if($hosts instanceof AttributeCollection) {
            $accept = function($host, $remote) use (&$CORS) {
                $host = var_export($host, true);
                $remote = var_export($remote, true);

                $CORS .= "CORS::registerHost($host, $remote);\n";
            };

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

$FILTERS
$ROOT
require 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use $APP_CLASS as Application;
use $BOOTSTRAP_CLASS as Bootstrap;
use Skyline\Kernel\Service\CORSService as CORS;

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

    protected function defineRoot(ProjectInterface $proj)
    {
        $ROOT = "";

        if ($attr = $proj->getAttribute(AttributeInterface::APP_ROOT_NAME)) {
            $root = $attr->getValue();
            $ROOT = "chdir(__DIR__ . \"/$root\");\n";
        }

        return $ROOT;
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