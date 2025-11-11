#!/bin/bash
#
# Installation script for Go Goroutines PECL Extension
# This script installs the extension and configures PHP
#

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

print_info() {
    echo -e "${BLUE}Info:${NC} $1"
}

# Check if running as root
check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "This script must be run as root (use sudo)"
        exit 1
    fi
}

# Check if extension is built
check_built() {
    if [ ! -f "modules/go_goroutines.so" ]; then
        print_error "Extension not built. Please run ./build.sh first."
        exit 1
    fi
}

# Install the extension
install_extension() {
    print_msg "Installing extension..."

    # Get PHP extension directory
    EXT_DIR=$(php-config --extension-dir)
    print_info "PHP extension directory: $EXT_DIR"

    # Copy the extension
    cp modules/go_goroutines.so "$EXT_DIR/"
    chmod 644 "$EXT_DIR/go_goroutines.so"

    print_msg "Extension installed to: $EXT_DIR/go_goroutines.so"
    echo ""
}

# Configure PHP
configure_php() {
    print_msg "Configuring PHP..."

    # Find PHP ini directory for additional configurations
    PHP_INI_DIR=$(php-config --ini-dir)

    if [ -z "$PHP_INI_DIR" ] || [ "$PHP_INI_DIR" = "" ]; then
        print_warning "Could not determine PHP ini directory"
        print_info "Please manually add 'extension=go_goroutines.so' to your php.ini"
        return
    fi

    print_info "PHP configuration directory: $PHP_INI_DIR"

    # Create configuration file
    CONF_FILE="$PHP_INI_DIR/20-go-goroutines.ini"

    cat > "$CONF_FILE" << EOF
; Configuration for Go Goroutines extension
extension=go_goroutines.so
EOF

    print_msg "Configuration file created: $CONF_FILE"
    echo ""
}

# Verify installation
verify_installation() {
    print_msg "Verifying installation..."

    # Check if extension is loaded
    if php -m | grep -q "go_goroutines"; then
        print_msg "✓ Extension is loaded successfully!"

        # Get extension info
        echo ""
        print_info "Extension information:"
        php --ri go_goroutines
        echo ""
    else
        print_error "Extension is not loaded. Please check your PHP configuration."
        print_info "You may need to restart your web server or PHP-FPM:"
        echo "  sudo systemctl restart apache2"
        echo "  sudo systemctl restart nginx"
        echo "  sudo systemctl restart php-fpm"
        exit 1
    fi
}

# Run a quick test
run_quick_test() {
    print_msg "Running quick test..."

    php -r '
    $id = go_start_goroutine();
    echo "Started goroutine: $id\n";
    if (go_wait($id, 5000)) {
        $result = go_get_result($id);
        echo "Result: $result\n";
        go_cleanup($id);
        echo "✓ Test passed!\n";
    } else {
        echo "✗ Test failed!\n";
        exit(1);
    }
    '

    echo ""
}

# Show next steps
show_next_steps() {
    echo ""
    echo "╔════════════════════════════════════════════════════════╗"
    echo "║       Installation completed successfully! 🎉         ║"
    echo "╚════════════════════════════════════════════════════════╝"
    echo ""
    echo "The extension is now installed and active!"
    echo ""
    echo "Try these commands:"
    echo ""
    echo "  1. Check if extension is loaded:"
    echo "     ${GREEN}php -m | grep go_goroutines${NC}"
    echo ""
    echo "  2. View extension info:"
    echo "     ${GREEN}php --ri go_goroutines${NC}"
    echo ""
    echo "  3. Run the demo:"
    echo "     ${GREEN}php examples/demo.php${NC}"
    echo ""
    echo "  4. Run tests:"
    echo "     ${GREEN}php tests/basic_test.php${NC}"
    echo "     ${GREEN}php tests/concurrent_test.php${NC}"
    echo ""
    echo "Need to restart your web server?"
    echo "  ${GREEN}sudo systemctl restart apache2${NC}"
    echo "  ${GREEN}sudo systemctl restart nginx${NC}"
    echo "  ${GREEN}sudo systemctl restart php-fpm${NC}"
    echo ""
}

# Main installation process
main() {
    echo "╔════════════════════════════════════════════════════════╗"
    echo "║   Go Goroutines PECL Extension - Install Script       ║"
    echo "╚════════════════════════════════════════════════════════╝"
    echo ""

    check_root
    check_built
    install_extension
    configure_php
    verify_installation
    run_quick_test
    show_next_steps
}

# Run main function
main
