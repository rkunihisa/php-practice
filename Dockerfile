FROM php:8.2-apache

# OSパッケージを更新して脆弱性を修正
RUN apt-get update && apt-get upgrade -y && apt-get dist-upgrade -y && apt-get autoremove -y && apt-get clean

# pdo_mysqlをインストール
RUN docker-php-ext-install pdo_mysql
