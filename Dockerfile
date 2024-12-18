FROM php:8.2-apache

# noninteractive frontend
ENV DEBIAN_FRONTEND=noninteractive

# update apt
RUN apt-get update 

# install dependencies
RUN apt-get -y install curl git vim unixodbc unixodbc-dev gnupg iputils-ping

# install ms apt key
RUN curl https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc

# add apt repo to sources (for debian 11)
RUN curl https://packages.microsoft.com/config/debian/11/prod.list | tee /etc/apt/sources.list.d/mssql-release.list

# update apt repos again
RUN apt-get update 

# unixodbc development headers
RUN sudo apt-get install -y unixodbc-dev

# kerberos library for debian-slim distributions
RUN sudo apt-get install -y libgssapi-krb5-2

# install odbc driver for MSSQL 18
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql18
RUN ACCEPT_EULA=Y apt-get install -y mssql-tools18

# add it to path
RUN echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
RUN source ~/.bashrc
# set timezone
RUN echo "America/New York" > /etc/timezone

# make the composer cache into a shared volume for persistence and sharing among derived images
VOLUME /var/www/html
