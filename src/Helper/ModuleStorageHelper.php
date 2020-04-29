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

namespace Skyline\Compiler\Helper;


use ArrayAccess;
use Countable;

class ModuleStorageHelper implements ArrayAccess, Countable
{
	private $storage = [];
	private $modules = [];
	private $moduleStack = [];

	public function pushModule(string $moduleName) {
		$this->moduleStack[] = $moduleName;
		if(!isset($this->modules[$moduleName]))
			$this->modules[$moduleName] = [];
	}

	public function popModule() {
		array_pop($this->moduleStack);
	}

	public function shiftModule() {
		array_shift($this->moduleStack);
	}

	public function resetModule() {
		$this->moduleStack = [];
	}

	public function getCurrentModule(): ?string {
		return end($this->moduleStack) ?: NULL;
	}

	public function getStorage() {
		return $this->storage;
	}

	public function getModuleStorages() {
		return $this->modules;
	}

	public function offsetExists($offset)
	{
		if($module = $this->getCurrentModule())
			return isset($this->modules[$module][$offset]);
		return isset($this->storage[$offset]);
	}

	public function &offsetGet($offset)
	{
		if($module = $this->getCurrentModule())
			return $this->modules[$module][$offset];
		return $this->storage[$offset];
	}

	public function offsetSet($offset, $value)
	{
		if($module = $this->getCurrentModule())
			$this->modules[$module][$offset] = $value;
		$this->storage[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		if($module = $this->getCurrentModule())
			unset( $this->modules[$module][$offset] );
		unset( $this->storage[$offset] );
	}

	public function count()
	{
		if($module = $this->getCurrentModule())
			return count( $this->modules[$module] );
		return count( $this->storage );
	}


	public function exportStorage($comment = "") {
		$data = var_export($this->getStorage(), true);

		if(class_exists("Skyline\Module\Compiler\ModuleCompiler")) {
			if($modules = $this->getModuleStorages()) {
				$modules = var_export($modules, true);
				$data = "<?php
use Skyline\\Module\\Loader\\ModuleLoader;
return ModuleLoader::dynamicallyCompile(function() {
return $data;
}, $modules);";
			} else
				$data = "<?php\n$comment\nreturn $data;";
		} else
			$data = "<?php\n$comment\nreturn $data;";

		return $data;
	}
}