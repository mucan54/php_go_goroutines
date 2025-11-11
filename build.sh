#!/bin/bash
#
# Build script for Go Goroutines PECL Extension
# This script builds both the Go library and PHP extension
#

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Print colored message
print_msg() {
    echo -e "${GREEN}==>${NC} $1"
}

print_error() {
    echo -e "${RED}Error:${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}Warning:${NC} $1"
}

# Check prerequisites
check_prerequisites() {
    print_msg "Checking prerequisites..."

    # Check for Go
    if ! command -v go &> /dev/null; then
        print_error "Go compiler not found. Please install Go 1.18 or higher."
        echo "  Download from: https://golang.org/dl/"
        exit 1
    fi

    GO_VERSION=$(go version | awk '{print $3}' | sed 's/go//')
    print_msg "Found Go version: $GO_VERSION"

    # Check for PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP not found. Please install PHP 7.4 or higher."
        exit 1
    fi

    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_msg "Found PHP version: $PHP_VERSION"

    # Check for phpize
    if ! command -v phpize &> /dev/null; then
        print_error "phpize not found. Please install PHP development headers."
        echo "  Ubuntu/Debian: sudo apt-get install php-dev"
        echo "  CentOS/RHEL:   sudo yum install php-devel"
        echo "  macOS:         brew install php"
        exit 1
    fi

    # Check for php-config
    if ! command -v php-config &> /dev/null; then
        print_error "php-config not found. Please install PHP development tools."
        exit 1
    fi

    # Check for GCC
    if ! command -v gcc &> /dev/null; then
        print_error "GCC compiler not found. Please install build-essential or gcc."
        exit 1
    fi

    print_msg "All prerequisites satisfied!"
    echo ""
}

# Build Go shared library
build_go_library() {
    print_msg "Building Go shared library..."

    cd go_src

    # Clean previous builds
    rm -f libgoroutines.so libgoroutines.h

    # Build the shared library
    go build -buildmode=c-shared -o libgoroutines.so goroutines.go

    if [ ! -f "libgoroutines.so" ]; then
        print_error "Failed to build Go shared library"
        exit 1
    fi

    GO_LIB_SIZE=$(du -h libgoroutines.so | cut -f1)
    print_msg "Go library built successfully! Size: $GO_LIB_SIZE"

    cd ..
    echo ""
}

# Build PHP extension
build_php_extension() {
    print_msg "Building PHP extension..."

    # Clean previous builds
    if [ -f "Makefile" ]; then
        make clean 2>/dev/null || true
    fi

    # Run phpize
    print_msg "Running phpize..."
    phpize

    # Configure
    print_msg "Configuring build..."
    ./configure --enable-go-goroutines

    # Make
    print_msg "Compiling..."
    make

    if [ ! -f "modules/go_goroutines.so" ]; then
        print_error "Failed to build PHP extension"
        exit 1
    fi

    EXT_SIZE=$(du -h modules/go_goroutines.so | cut -f1)
    print_msg "PHP extension built successfully! Size: $EXT_SIZE"
    echo ""
}

# Display next steps
show_next_steps() {
    echo ""
    echo "╔════════════════════════════════════════════════════════╗"
    echo "║          Build completed successfully! 🎉              ║"
    echo "╚════════════════════════════════════════════════════════╝"
    echo ""
    echo "Next steps:"
    echo ""
    echo "  1. Install the extension:"
    echo "     ${GREEN}sudo make install${NC}"
    echo ""
    echo "  2. Enable the extension by adding to php.ini:"
    echo "     ${GREEN}extension=go_goroutines.so${NC}"
    echo ""
    echo "  3. Or load it dynamically:"
    echo "     ${GREEN}php -d extension=modules/go_goroutines.so your_script.php${NC}"
    echo ""
    echo "  4. Run tests:"
    echo "     ${GREEN}make test${NC}"
    echo ""
    echo "  5. Try the demo:"
    echo "     ${GREEN}php -d extension=modules/go_goroutines.so examples/demo.php${NC}"
    echo ""
}

# Main build process
main() {
    echo "╔════════════════════════════════════════════════════════╗"
    echo "║   Go Goroutines PECL Extension - Build Script         ║"
    echo "╚════════════════════════════════════════════════════════╝"
    echo ""

    check_prerequisites
    build_go_library
    build_php_extension
    show_next_steps
}

# Run main function
main
