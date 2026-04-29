FROM moodlehq/moodle-php-apache:8.2

RUN apt-get update && apt-get install -y wget && rm -rf /var/lib/apt/lists/*

# Download Moodle 4.4.0 (~60 MB compressed)
RUN wget -qO /tmp/moodle.tar.gz \
      "https://github.com/moodle/moodle/archive/refs/tags/v4.4.0.tar.gz" \
    && tar -xzf /tmp/moodle.tar.gz -C /var/www/html --strip-components=1 \
    && rm /tmp/moodle.tar.gz

RUN mkdir -p /var/moodledata /var/moodleconfig \
    && chown -R www-data:www-data /var/www/html /var/moodledata /var/moodleconfig

RUN echo 'export APACHE_DOCUMENT_ROOT=/var/www/html' >> /etc/apache2/envvars \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
