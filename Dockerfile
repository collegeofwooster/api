FROM php:7-apache

# noninteractive frontend
ENV DEBIAN_FRONTEND=noninteractive

# install development packages
# To avoid prompt for new config: http://stackoverflow.com/a/23048987/4126114
# Note that for php7, replace
#  * libapache2-mod-php5 with php-mbstring
#  * php5-odbc with php-odbc
RUN apt-get update > /dev/null 

RUN apt-get -y install curl git vim unixodbc unixodbc-dev gnupg

# cannot install odbc wihtout the below because of https://github.com/docker-library/php/issues/103#issuecomment-160772802
RUN set -x \
    && cd /usr/src/ && tar -xf php.tar.xz && mv php-7* php \
    && cd /usr/src/php/ext/odbc \
    && phpize \
    && sed -ri 's@^ *test +"\$PHP_.*" *= *"no" *&& *PHP_.*=yes *$@#&@g' configure \
    && ./configure --with-unixODBC=shared,/usr > /dev/null \
    && docker-php-ext-install odbc > /dev/null

# install ms apt key
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -

# add apt repo to sources (for debian 11)
RUN curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list

# update apt repos again
RUN apt-get update > /dev/null 

# install odbc driver for MSSQL 17
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql17
RUN ACCEPT_EULA=Y apt-get install -y mssql-tools

# add it to path
RUN echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc

# set timezone
RUN echo "America/New York" > /etc/timezone

# make the composer cache into a shared volume for persistence and sharing among derived images
VOLUME /var/www/html