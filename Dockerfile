FROM hyperf/hyperf:8.3-alpine-v3.19-swoole
LABEL maintainer="MineManage Developers <group@stye.cn>" version="1.0" license="MIT" app.name="MineManage"

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Kolkata"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true)

# update
RUN set -ex \
    # show php version and extensions
    && php -v \
    && php -m \
    && php --ri swoole \
    #  ---------- some config ----------
    && cd /etc/php* \
    # - config PHP
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

RUN apk --no-cache --no-progress update \
    && apk --no-cache --no-progress upgrade \
      && apk add --no-cache php83-gmp


ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

WORKDIR /opt/www

COPY . /opt/www

RUN composer --version && composer install --no-dev -o

EXPOSE 9501 9502 9503

ENTRYPOINT ["php", "/opt/www/start.php", "start"]
