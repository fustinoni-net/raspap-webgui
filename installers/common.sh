raspap_dir="/etc/raspap"
raspap_user="www-data"
version=`sed 's/\..*//' /etc/debian_version`

# Determine version, set default home location for lighttpd and 
# php package to install 
webroot_dir="/var/www/html" 

if [ $version -eq 9 ]; then
    version_msg="Raspian 9.0 (Stretch)" 
    php_package="php7.0-cgi" 
elif [ $version -eq 8 ]; then 
    version_msg="Raspian 8.0 (Jessie)" 
    php_package="php5-cgi" 
else 
    version_msg="Raspian earlier than 8.0 (Wheezy)"
    webroot_dir="/var/www" 
    php_package="php5-cgi" 
fi

phpcgiconf=""
if [ "$php_package" = "php7.0-cgi" ]; then
    phpcgiconf="/etc/php/7.0/cgi/php.ini"
elif [ "$php_package" = "php5-cgi" ]; then
    phpcgiconf="/etc/php5/cgi/php.ini"
fi

# Outputs a RaspAP Install log line
function install_log() {
    echo -e "\033[1;32mRaspAP Install: $*\033[m"
}

# Outputs a RaspAP Install Error log line and exits with status code 1
function install_error() {
    echo -e "\033[1;37;41mRaspAP Install Error: $*\033[m"
    exit 1
}

# Outputs a RaspAP Warning line
function install_warning() {
    echo -e "\033[1;33mWarning: $*\033[m"
}

# Outputs a welcome message
function display_welcome() {
    raspberry='\033[0;35m'
    green='\033[1;32m'

    echo -e "${raspberry}\n"
    echo -e " 888888ba                              .d888888   888888ba" 
    echo -e " 88     8b                            d8     88   88     8b" 
    echo -e "a88aaaa8P' .d8888b. .d8888b. 88d888b. 88aaaaa88a a88aaaa8P" 
    echo -e " 88    8b. 88    88 Y8ooooo. 88    88 88     88   88" 
    echo -e " 88     88 88.  .88       88 88.  .88 88     88   88" 
    echo -e " dP     dP  88888P8  88888P  88Y888P  88     88   dP" 
    echo -e "                             88"                             
    echo -e "                             dP       Ermes edition"
    echo -e "${green}"
    echo -e "The Quick Installer will guide you through a few easy steps\n\n"
}

### NOTE: all the below functions are overloadable for system-specific installs
### NOTE: some of the below functions MUST be overloaded due to system-specific installs

function config_installation() {
    install_log "Configure installation"
    echo "Detected ${version_msg}" 
    echo "Install directory: ${raspap_dir}"
    echo "Lighttpd directory: ${webroot_dir}"
    echo -n "Complete installation with these values? [y/N]: "
    read answer
    if [[ $answer != "y" ]]; then
        echo "Installation aborted."
        exit 0
    fi
}

# Runs a system software update to make sure we're using all fresh packages
function update_system_packages() {
    # OVERLOAD THIS
    install_error "No function definition for update_system_packages"
}

# Installs additional dependencies using system package manager
function install_dependencies() {
    # OVERLOAD THIS
    install_error "No function definition for install_dependencies"
}

# Enables PHP for lighttpd and restarts service for settings to take effect
function enable_php_lighttpd() {
    install_log "Enabling PHP for lighttpd"


    sudo lighttpd-enable-mod fastcgi-php    
    sudo service lighttpd force-reload
    sudo /etc/init.d/lighttpd restart || install_error "Unable to restart lighttpd"
}

# Verifies existence and permissions of RaspAP directory
function create_raspap_directories() {
    install_log "Creating RaspAP directories"
    if [ -d "$raspap_dir" ]; then
        sudo mv $raspap_dir "$raspap_dir.`date +%F-%R`" || install_error "Unable to move old '$raspap_dir' out of the way"
    fi
    sudo mkdir -p "$raspap_dir" || install_error "Unable to create directory '$raspap_dir'"

    # Create a directory for existing file backups.
    sudo mkdir -p "$raspap_dir/backups"

    # Create a directory to store networking configs
    #sudo mkdir -p "$raspap_dir/networking"
    # Copy existing dhcpcd.conf to use as base config
    #cat /etc/dhcpcd.conf | sudo tee -a /etc/raspap/networking/defaults

    sudo chown -R $raspap_user:$raspap_user "$raspap_dir" || install_error "Unable to change file ownership for '$raspap_dir'"
}



# Fetches latest files from github to webroot
function download_latest_files() {
    if [ -d "$webroot_dir" ]; then
        sudo mv $webroot_dir "$webroot_dir.`date +%F-%R`" || install_error "Unable to remove old webroot directory"
    fi

    install_log "Cloning latest files from github"
    #git clone --depth 1 https://github.com/fustinoni-net/raspap-webgui /tmp/raspap-webgui || install_error "Unable to download files from github"
    git clone https://github.com/fustinoni-net/raspap-webgui /tmp/raspap-webgui || install_error "Unable to download files from github"
    git -C /tmp/raspap-webgui/ checkout dev
    sudo mv /tmp/raspap-webgui $webroot_dir || install_error "Unable to move raspap-webgui to web root"

}

# Sets files ownership in web root directory
function change_file_ownership() {
    if [ ! -d "$webroot_dir" ]; then
        install_error "Web root directory doesn't exist"
    fi

    install_log "Changing file ownership in web root directory"
    sudo chown -R $raspap_user:$raspap_user "$webroot_dir" || install_error "Unable to change file ownership for '$webroot_dir'"
}


# Move configuration file to the correct location
function move_config_file() {
    if [ ! -d "$raspap_dir" ]; then
        install_error "'$raspap_dir' directory doesn't exist"
    fi

    install_log "Moving configuration file to '$raspap_dir'"
    sudo mv "$webroot_dir"/raspap.php "$raspap_dir" || install_error "Unable to move files to '$raspap_dir'"
    sudo chown -R $raspap_user:$raspap_user "$raspap_dir" || install_error "Unable to change file ownership for '$raspap_dir'"
}

# Add a single entry to the sudoers file
function sudo_add() {
    sudo bash -c "echo \"www-data ALL=(ALL) NOPASSWD:$1\" | (EDITOR=\"tee -a\" visudo)" \
        || install_error "Unable to patch /etc/sudoers"
}

# Adds www-data user to the sudoers file with restrictions on what the user can execute
function  patch_system_files() {

# Ricorda i permessi sudo chmod -R ugo=rw client su /etc/openvpn/client/



    # add symlink to prevent wpa_cli cmds from breaking with multiple wlan interfaces
    #install_log "symlinked wpa_supplicant hooks for multiple wlan interfaces"
    #sudo ln -s /usr/share/dhcpcd/hooks/10-wpa_supplicant /etc/dhcp/dhclient-enter-hooks.d/
    # Set commands array
    cmds=(
        "/sbin/ifdown"
        "/sbin/ifup"
        "/bin/cat /etc/wpa_supplicant/wpa_supplicant.conf"
        "/bin/cat /etc/wpa_supplicant/wpa_supplicant-wlan[0-9].conf"
        "/bin/cp /tmp/wifidata /etc/wpa_supplicant/wpa_supplicant.conf"
        "/bin/cp /tmp/wifidata /etc/wpa_supplicant/wpa_supplicant-wlan[0-9].conf"
        "/sbin/wpa_cli -i wlan[0-9] scan_results"
        "/sbin/wpa_cli -i wlan[0-9] scan"
        "/sbin/wpa_cli -i wlan[0-9] reconfigure"
	    "/sbin/wpa_cli -i wlan[0-9] select_network"
        "/bin/cp /tmp/hostapddata /etc/hostapd/hostapd.conf"
        "/etc/init.d/hostapd start"
        "/etc/init.d/hostapd stop"
        "/etc/init.d/dnsmasq start"
        "/etc/init.d/dnsmasq stop"
        "/bin/cp /tmp/dhcpddata /etc/dnsmasq.conf"
        "/sbin/shutdown -h now"
        "/sbin/reboot"
        "/sbin/ip link set wlan[0-9] down"
        "/sbin/ip link set wlan[0-9] up"
        "/sbin/ip -s a f label wlan[0-9]"
        "/bin/cp /etc/raspap/networking/dhcpcd.conf /etc/dhcpcd.conf"
        "/etc/raspap/hostapd/enablelog.sh"
        "/etc/raspap/hostapd/disablelog.sh"
        "/usr/sbin/openvpn"
#        "/home/pi/wifiExtender/utils/system/setVPNRoute.sh"
#        "/home/pi/wifiExtender/utils/system/removeVPNRoute.sh"
#        "/home/pi/wifiExtender/utils/system/isBaseRouteEnable.sh"
#        "/home/pi/wifiExtender/utils/system/setBaseRoute.sh"
#        "/home/pi/wifiExtender/utils/system/removeBaseRoute.sh"
#        "/home/pi/wifiExtender/setDnsMasqOptions.sh"
        ${ERMES_INSTALL_DIR}"utils/system/setVPNRoute.sh"
        ${ERMES_INSTALL_DIR}"utils/system/removeVPNRoute.sh"
        ${ERMES_INSTALL_DIR}"utils/system/isBaseRouteEnable.sh"
        ${ERMES_INSTALL_DIR}"utils/system/setBaseRoute.sh"
        ${ERMES_INSTALL_DIR}"utils/system/removeBaseRoute.sh"
        ${ERMES_INSTALL_DIR}"setDnsMasqOptions.sh"


    )

    # Check if sudoers needs patching
    if [ $(sudo grep -c www-data /etc/sudoers) -ne 28 ]
    then
        # Sudoers file has incorrect number of commands. Wiping them out.
        install_log "Cleaning sudoers file"
        sudo sed -i '/www-data/d' /etc/sudoers
        install_log "Patching system sudoers file"
        # patch /etc/sudoers file
        for cmd in "${cmds[@]}"
        do
            sudo_add $cmd
            IFS=$'\n'
        done
    else
        install_log "Sudoers file already patched"
    fi

    chgrp -R $raspap_user /etc/openvpn/client/
    chmod -R g+w /etc/openvpn/client/
    usermod -a -G netdev $raspap_user
}


# Optimize configuration of php-cgi.
function optimize_php() {
    install_log "Optimize PHP configuration"
    if [ ! -f "$phpcgiconf" ]; then
        install_warning "PHP configuration could not be found."
        return
    fi

    # Backup php.ini and create symlink for restoring.
    datetimephpconf=$(date +%F-%R)
    sudo cp "$phpcgiconf" "$raspap_dir/backups/php.ini.$datetimephpconf"
    sudo ln -sf "$raspap_dir/backups/php.ini.$datetimephpconf" "$raspap_dir/backups/php.ini"

    echo -n "Enable HttpOnly for session cookies (Recommended)? [Y/n]: "
    read answer
    if [ "$answer" != 'n' ] && [ "$answer" != 'N' ]; then
        echo "Php-cgi enabling session.cookie_httponly."
        sudo sed -i -E 's/^session\.cookie_httponly\s*=\s*(0|([O|o]ff)|([F|f]alse)|([N|n]o))\s*$/session.cookie_httponly = 1/' "$phpcgiconf"
    fi

    if [ "$php_package" = "php7.0-cgi" ]; then
        echo -n "Enable PHP OPCache? [Y/n]: "
        read answer
        if [ "$answer" != 'n' ] && [ "$answer" != 'N' ]; then
            echo "Php-cgi enabling opcache.enable."
            sudo sed -i -E 's/^;?opcache\.enable\s*=\s*(0|([O|o]ff)|([F|f]alse)|([N|n]o))\s*$/opcache.enable = 1/' "$phpcgiconf"
            # Make sure opcache extension is turned on.
            if [ -f "/usr/sbin/phpenmod" ]; then
                sudo phpenmod opcache
            else
                install_warning "phpenmod not found."
            fi
        fi
    fi
}

function install_complete() {
    install_log "Installation completed!"

    echo -n "The system needs to be rebooted as a final step. Reboot now? [y/N]: "
    read answer
    if [[ $answer != "y" ]]; then
        echo "Installation reboot aborted."
        exit 0
    fi
    sudo shutdown -r now || install_error "Unable to execute shutdown"
}

function install_raspap() {
    display_welcome
    config_installation
    update_system_packages
    install_dependencies
    optimize_php
    enable_php_lighttpd
    create_raspap_directories
    download_latest_files
    change_file_ownership
    move_config_file
    patch_system_files
    install_complete
}
