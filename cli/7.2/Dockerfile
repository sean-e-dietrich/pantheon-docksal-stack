FROM docksal/cli:2.1-php7.2

# Version information
ENV WKHTMLTOPDF_VERSION 0.12.2
ENV PHANTOMJS_VERSION 2.1.1
ENV MAVEN_VERSION 3.5.3

# Install the additional things that make the pantheon
RUN apt-get update && apt-get install -y \
    xfonts-base \
    xfonts-75dpi \
    openjdk-7-jre-headless \
    openjdk-7-jdk \
    bzip2 \
    exiftool \
    git-core \
    imagemagick \
    libbz2-dev \
    libc-client2007e-dev \
    libjpeg-dev \
    libkrb5-dev \
    libldap2-dev \
    libmagickwand-dev \
    libmcrypt-dev \
    libmemcached-dev \
    libpng12-0 \
    libpq-dev \
    libxml2-dev \
    libicu-dev \
    mysql-client \
    postgresql-client \
    pv \
    ssh \
    unzip \
    wget \
    xfonts-base \
    xfonts-75dpi \
    zlib1g-dev \
    && pecl install apcu \
    && pecl install oauth-2.0.2 \
    && docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
    && docker-php-ext-enable apcu \
    && docker-php-ext-enable oauth \
    && docker-php-ext-install \
      gd \
      mbstring \
  && docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
  && docker-php-ext-install gd \
  && rm -f /usr/local/etc/php/conf.d/*-memcached.ini \
  && mkdir -p /srv/bin \
  && cd /tmp && curl -OL "https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/${WKHTMLTOPDF_VERSION}/wkhtmltox-${WKHTMLTOPDF_VERSION}_linux-jessie-amd64.deb" \
  && dpkg -i /tmp/wkhtmltox-${WKHTMLTOPDF_VERSION}_linux-jessie-amd64.deb \
  && mkdir -p /srv/bin && ln -s /usr/local/bin/wkhtmltopdf /srv/bin/wkhtmltopdf \
  && cd /srv/bin \
  && curl -fsSL "https://github.com/Medium/phantomjs/releases/download/v${PHANTOMJS_VERSION}/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64.tar.bz2" | tar -xjv \
  && mv phantomjs-${PHANTOMJS_VERSION}-linux-x86_64/bin/phantomjs /srv/bin/phantomjs \
  && rm -rf phantomjs-${PHANTOMJS_VERSION}-linux-x86_64 && rm -f phantomjs-${PHANTOMJS_VERSION}-linux-x86_64.tar.bz2 \
  && chmod +x /srv/bin/phantomjs \
  && curl -fsSL "http://www-us.apache.org/dist/maven/maven-3/${MAVEN_VERSION}/binaries/apache-maven-${MAVEN_VERSION}-bin.tar.gz" | tar -xz -C /tmp \
  && cd /tmp && curl -OL "http://archive.apache.org/dist/tika/apache-tika-1.1-src.zip" \
  && unzip /tmp/apache-tika-1.1-src.zip \
  && rm /tmp/apache-tika-1.1-src.zip \
  && cd /tmp/apache-tika-1.1 && /tmp/apache-maven-${MAVEN_VERSION}/bin/mvn install \
  && cp -rf /tmp/apache-tika-1.1/tika-app/target/tika-app-1.1.jar /srv/bin/tika-app-1.1.jar \
  && apt-get -y remove openjdk-8-jdk \
  && apt-get -y clean \
  && apt-get -y autoclean \
  && apt-get -y autoremove \
  && rm -rf /var/lib/apt/lists/* && rm -rf && rm -rf /var/lib/cache/* && rm -rf /var/lib/log/* && rm -rf /tmp/*
  
RUN rm -rf /usr/local/etc/php/conf.d/zzz-php.ini \
  && rm -rf /usr/local/etc/php-fpm.d/zzz-php-fpm.conf

COPY config/php.ini /usr/local/etc/php/conf.d/zzz-php.ini
COPY config/php-fpm.conf /usr/local/etc/php-fpm.d/zzz-php-fpm.conf

RUN mkdir /opt/includes
COPY config/prepend.php /opt/includes/prepend.php