<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>common</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/common/v)](https://packagist.org/packages/apie/common) [![Total Downloads](https://poser.pugx.org/apie/common/downloads)](https://packagist.org/packages/apie/common) [![Latest Unstable Version](https://poser.pugx.org/apie/common/v/unstable)](https://packagist.org/packages/apie/common) [![License](https://poser.pugx.org/apie/common/license)](https://packagist.org/packages/apie/common) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-common.svg)](https://apie-lib.github.io/projectCoverage/common/index.html)  

[![PHP Composer](https://github.com/apie-lib/common/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/common/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
This package contains common actions used by the high-level functionality (where [apie/core](https://packagist.org/packages/apie/core) contains low-level functionality). For example [apie/rest-api](https://packagist.org/packages/apie/rest-api) uses the actions from this package and maps them to REST API calls.

### CreateObjectAction
Creates objects from raw contents and stores them with the persistence layer.

### GetListAction
retrieves a list of objects of a specific resource. It can filter them too.

### RunAction
The RPC type of action. Runs a method and returns the return value.
