# console
package for running configuration and processing tasks for cymapgt modules via command line interface

## Description

The console package allows other packages to implement an interface that allows them to
provide command line functionality to their classes in with minimal duplication of code.
A NetConsoleInterface aware class can be registered to the console API; which offers
among other things automatic help generation as well as an authentication interface
prior to accessing registered namespaces.

## Installing

### Install application via Composer

    require "cymapgt/console" : "^1.0.0"

## Usage

### Overview

- Authenticate the console user (named console) to the Service

- Provide interface to add services to the API

- Provide interface to remove services from the API

- Provide interface to amend a service using the API

- Provide interface to display usage for the API

- Provide interface to delegate actual API calls to the registered services

### Using the Console package

#### Writing a NetConsole aware class
The package comes with two demo Classes that implement the NetConsoleInterface.
See the files src/DemoApi.php and src/DemoRpi.php for template which you can use
to implement your NetConsole class. Copy the files and customize as required.

#### Authentication to NetConsole

The default NetConsole authentication comes with some assumptions:

1.You auth data store is a database, with a table named user; and fields for
username and password

2.You have an active user named console in the datastore
    
If you adhere to the above, your console authentication will work out of the
box (assuming as well that the NetworkBootstrap is properly configured).

If you would like to implement your own Authentication, replace the file
src/helper/ConsoleCredentials.php and in it

1.Create a class named ConsoleCredentials

2.Creat a public static method named authenticate() that will implement your
custom authentication logic

#### Core NetConsole Commands
The core of NetConsole implements the NetConsoleInterface. There are a number
of reserved keywords:

1.**ADD COMMAND:**

add serviceName serviceNamespace

**COMMANDS AND SWITCHES:**

*COMMANDS*

serviceName	 - The name of the service as registered in the console, or one of the sticky services.

serviceNamespace - The fully qualified namespace for the service being registered to the netconsole API

*SWITCHES*

None

**EXAMPLE:**

add bootstrap cymapgt\core\utility\bootstrap\console\BootstrapConsole;


2.**HELP COMMAND:**

help serviceName [-v="..."] [-m="..."]

**COMMANDS AND SWITCHES:**

*COMMANDS*

serviceName	 - The name of the service as registered in the console, or one of the sticky services.

*SWITCHES*

 -v	 - The verbosity level of the help documentation

 -m	 - Specifying a method name here will filter the help to provide only docs for that specific method

**EXAMPLES**

help bootstrap -m=config-all -v=3

help add

### Testing

PHPUnit Tests are provided with the package

### Contribute

* Email @rhossis or contact via Skype
* You will be added as author for contributions

### License

PROPRIETARY
