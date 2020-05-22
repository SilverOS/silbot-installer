# Silbot Installer

An easy installer for [Silbot-Webhook](https://github.com/SilverOS/Silbot-Webhook).
- - -
# Purpose

This installer is made to make normal boring processes like setting the Webhook, copying files and installing the database easier and faster for Silbot.
- - -
# How To Use

To use it just drop installer.php file in the root of your webserver, **edit the password in it**, and **visit it from HTTPS**, it will automatically load the interface from my server, if you want to host the UI by yourself, just download the UI folder and edit the installer.php file to use it.
After compile the information about your bot, the directory where you want to install it, the bot token, and MYSQL credentials (optional).
- - -
# How It Works

When you submit the from, the installer creates the chosen directory and downloads there all the files of the framework and edit the [config.php](https://github.com/SilverOS/Silbot-Webhook/wiki/Config) file with MySQL credentials.
After that the **webhook is set using the domain you are visiting the installer from** and the database is installed.
- - -
# Security

**PLEASE NOTE:** this installer is dangerous, it injects code in your webserver and it can be used for malicious purpose, after the installation please **hide the file or remove it and chose a strong password**.
