# Skyline CMS Compiler
Skyline CMS boots from a compiled file structure.  
The compilation allows it to increase performance while delivering your website, but also, detect possible bug before they occur.

When you are creating a project using composer, you do not need to require the compiler.  
in fact, creating projects in development, includes the compiler package automatically.

### Direct install
````bin
$ composer require skyline/compiler
````
You can setup several configuration to automatically compile within your IDE, but Skyline CMS will not compile automatically.

Note that the compiler is not designed for performance, so you shoult not use it before responding requests.  
In fact, using the compiler in your web application is only recommended inside the administrator panel where you can inform the client about the compiling process.
