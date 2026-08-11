FROM moodlehq/moodle-php-apache:8.2

RUN apt-get update && apt-get install -y wget && rm -rf /var/lib/apt/lists/*

# Download Moodle 4.4.0 (~60 MB compressed)
RUN wget -qO /tmp/moodle.tar.gz \
      "https://github.com/moodle/moodle/archive/refs/tags/v4.4.0.tar.gz" \
    && tar -xzf /tmp/moodle.tar.gz -C /var/www/html --strip-components=1 \
    && rm /tmp/moodle.tar.gz

# auth_userkey: one-time-key SSO used to log prospects into Moodle without a password,
# so course progress can be tracked against a shadow user keyed by prospect_form_id.
RUN mkdir -p /var/www/html/auth/userkey \
    && wget -qO /tmp/auth_userkey.tar.gz \
      "https://github.com/catalyst/moodle-auth_userkey/archive/refs/heads/MOODLE_33PLUS.tar.gz" \
    && tar -xzf /tmp/auth_userkey.tar.gz -C /var/www/html/auth/userkey --strip-components=1 \
    && rm /tmp/auth_userkey.tar.gz \
    && chown -R www-data:www-data /var/www/html/auth/userkey

RUN mkdir -p /var/moodledata /var/moodleconfig \
    && chown -R www-data:www-data /var/www/html /var/moodledata /var/moodleconfig

RUN echo 'export APACHE_DOCUMENT_ROOT=/var/www/html' >> /etc/apache2/envvars \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
