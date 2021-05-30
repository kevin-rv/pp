.PHONY: tests
tests: ## Run tests
	vendor/bin/phpunit --testdox

.PHONY: phpcs-fix
phpcs-fix: ## Run php-cs fix
	vendor/bin/php-cs-fixer fix

.PHONY: phpcs-dry-run
phpcs-dry-run: ## Run php-cs fix - dry-run mode
	vendor/bin/php-cs-fixer fix --dry-run

.PHONY: help
help: ## Display this help message
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: reset-db-struct
reset-db-struct: ## Reset db and original migrations (drop, create, migration and fixtures clean-up)
	bin/console do:da:dr --force
	bin/console do:da:cr
	rm -rf migrations/*
	bin/console ma:mi
	echo yes | bin/console do:mi:mi
	echo yes | bin/console do:fi:lo
