FROM ubuntu:24.04

# noninteractive frontend
ENV DEBIAN_FRONTEND=noninteractive

# update apt
RUN apt-get update

# install some dependencies
RUN apt install -y curl software-properties-common iputils-ping

# install php
RUN add-apt-repository ppa:ondrej/php -y
RUN apt update
RUN apt install -y php8.1 php8.1-dev php8.1-xml unixodbc-dev

# install apache
RUN apt install -y libapache2-mod-php8.1 apache2
RUN a2dismod mpm_event
RUN a2enmod mpm_prefork
RUN a2enmod php8.1

# install the sqlsrv php extension and enable it
RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv
RUN printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php/8.1/mods-available/sqlsrv.ini
RUN printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php/8.1/mods-available/pdo_sqlsrv.ini
RUN phpenmod -v 8.1 sqlsrv pdo_sqlsrv

# install MS apt key
RUN curl https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg

# add MS apt repo
RUN curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list | tee /etc/apt/sources.list.d/mssql-release.list

# update apt repos again
RUN apt update

# install the sql driver
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql18
RUN ACCEPT_EULA=Y apt-get install -y mssql-tools18
RUN echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc

# set timezone
RUN echo "America/New York" > /etc/timezone

# expose port 80
EXPOSE 80

# run apache foreground so the container stays running
CMD apachectl -DFOREGROUND

# make the composer cache into a shared volume for persistence and sharing among derived images
VOLUME /var/www/html