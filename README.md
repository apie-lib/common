<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>common</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/common/v)](https://packagist.org/packages/apie/common) [![Total Downloads](https://poser.pugx.org/apie/common/downloads)](https://packagist.org/packages/apie/common) [![Latest Unstable Version](https://poser.pugx.org/apie/common/v/unstable)](https://packagist.org/packages/apie/common) [![License](https://poser.pugx.org/apie/common/license)](https://packagist.org/packages/apie/common) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-common.svg)](https://apie-lib.github.io/projectCoverage/common/index.html)  

[![PHP Composer](https://github.com/apie-lib/common/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/common/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
This package contains the generic, framework-agnostic actions used by the higher-level Apie packages (where [apie/core](https://packagist.org/packages/apie/core) contains the low-level building blocks). For example [apie/rest-api](https://packagist.org/packages/apie/rest-api) and [apie/cms](https://packagist.org/packages/apie/cms) map these actions to HTTP endpoints and admin panel screens. It also ships shared services such as `ApieFacade`, `LoginService`, basic auth support, audit logging events, and the menu structure used by CMS-like frontends.

### Standalone usage
Install it with:
```bash
composer require apie/common
```

| Action | Description |
| --- | --- |
| `CreateObjectAction` | Creates an object from raw contents and stores it with the persistence layer. |
| `GetItemAction` | Retrieves a single object of a resource by its identifier. |
| `GetListAction` | Retrieves a (filterable) list of objects of a resource. |
| `ModifyObjectAction` | Updates an existing object with raw contents. |
| `RemoveObjectAction` | Removes an object from the persistence layer. |
| `RunAction` | The RPC-style action: runs a method on the bounded context and returns its return value. |
| `RunItemMethodAction` | Runs a method on a single entity instance and returns its return value. |
| `StreamItemMethodAction` | Runs a method on a single entity instance and streams the return value (e.g. file downloads). |

### Symfony integration
Via `apie/apie-bundle`, `common.yaml` and `add_basic_auth.yaml` are loaded automatically and register `Apie\Common\ApieFacade`, the actions above, context builders, the audit log event subscriber, and the login/basic-auth services. Configuration keys such as `apie.bounded_contexts`, `apie.encryption_key`, `apie.cms.base_url` and `apie.rest_api.base_url` (set in `config/packages/apie.yaml`) feed these services.

### Laravel integration
Via `apie/laravel-apie`, the generated `Apie\Common\CommonServiceProvider` (and `Apie\Common\AddBasicAuthServiceProvider`) are auto-registered and wire the same services and console commands into the Laravel container.
