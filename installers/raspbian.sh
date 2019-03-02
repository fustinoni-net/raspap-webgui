
ERMES_INSTALL_DIR = $1


UPDATE_URL="https://raw.githubusercontent.com/fustinoni-net/raspap-webgui/dev/"
wget -q ${UPDATE_URL}/installers/common.sh -O /tmp/raspapcommon.sh
source /tmp/raspapcommon.sh && rm -f /tmp/raspapcommon.sh

function update_system_packages() {
    install_log "Updating sources"
    sudo apt-get update || install_error "Unable to update package list"
}

function install_dependencies() {
    install_log "Installing required packages"
    sudo apt-get install lighttpd $php_package vnstat || install_error "Unable to install dependencies"
}

install_raspap
