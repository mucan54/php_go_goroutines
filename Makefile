.PHONY: all build clean install test go-build help

# Detect PHP config
PHP_CONFIG ?= $(shell which php-config)
PHPIZE ?= $(shell which phpize)

# Go settings
GO ?= $(shell which go)
GO_SRC_DIR = go_src
GO_LIB = $(GO_SRC_DIR)/libgoroutines.so
GO_SRC = $(GO_SRC_DIR)/goroutines.go

# Build targets
all: build

help:
	@echo "Go Goroutines PECL Extension - Build System"
	@echo ""
	@echo "Available targets:"
	@echo "  make build      - Build the Go library and PHP extension"
	@echo "  make go-build   - Build only the Go shared library"
	@echo "  make clean      - Clean build artifacts"
	@echo "  make install    - Install the extension (requires sudo)"
	@echo "  make test       - Run test scripts"
	@echo "  make help       - Show this help message"
	@echo ""
	@echo "Prerequisites:"
	@echo "  - Go 1.18 or higher"
	@echo "  - PHP 7.4 or higher with php-dev/php-devel"
	@echo "  - GCC compiler"
	@echo ""

go-build:
	@echo "Building Go shared library..."
	@if [ ! -x "$(GO)" ]; then \
		echo "Error: Go compiler not found. Please install Go 1.18+"; \
		exit 1; \
	fi
	cd $(GO_SRC_DIR) && $(GO) build -buildmode=c-shared -o libgoroutines.so goroutines.go
	@echo "Go library built successfully: $(GO_LIB)"

build: go-build
	@echo "Building PHP extension..."
	@if [ ! -x "$(PHPIZE)" ]; then \
		echo "Error: phpize not found. Please install php-dev/php-devel"; \
		exit 1; \
	fi
	$(PHPIZE)
	./configure --enable-go-goroutines
	$(MAKE) -f Makefile.frag
	@echo ""
	@echo "Build complete! The extension is in: modules/go_goroutines.so"
	@echo "To install, run: sudo make install"

clean:
	@echo "Cleaning build artifacts..."
	-$(PHPIZE) --clean
	-rm -f $(GO_LIB) $(GO_SRC_DIR)/libgoroutines.h
	-rm -rf modules/ .libs/ autom4te.cache/
	-rm -f acinclude.m4 aclocal.m4 config.guess config.h config.h.in config.log
	-rm -f config.nice config.status config.sub configure configure.ac install-sh
	-rm -f libtool ltmain.sh Makefile.frag Makefile.global Makefile.objects
	-rm -f missing mkinstalldirs run-tests.php *.lo *.la
	-find . -name "*.o" -delete
	@echo "Clean complete!"

install: build
	@echo "Installing extension..."
	$(MAKE) -f Makefile.frag install
	@echo ""
	@echo "Extension installed successfully!"
	@echo "Add 'extension=go_goroutines.so' to your php.ini to enable it."
	@echo ""
	@echo "To verify installation, run:"
	@echo "  php -m | grep go_goroutines"

test:
	@echo "Running test scripts..."
	@if [ ! -f "modules/go_goroutines.so" ]; then \
		echo "Error: Extension not built. Run 'make build' first."; \
		exit 1; \
	fi
	php -d extension=modules/go_goroutines.so tests/basic_test.php
	php -d extension=modules/go_goroutines.so tests/concurrent_test.php
	php -d extension=modules/go_goroutines.so examples/demo.php
