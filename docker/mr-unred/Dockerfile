FROM debian:9.9

ARG WWWGROUP
#ARG NODE_VERSION 16

WORKDIR /var/www/html
RUN pwd && ls -al

# apt install 時にダイアログボックス開いて失敗する輩がいるから
# このおまじないをつけるらしい
# 実行時環境引数にすべきかもしれない
ENV DEBIAN_FRONTEND noninteractive

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/kicaktime && echo $TZ > /etc/timezone
RUN sed -i 's@deb.debian.org@ftp.jaist.ac.jp/pub/Linux@g' /etc/apt/sources.list \
    && apt-get update \
    && apt-get purge -y man \
    && apt-get install -y aria2 wget \
    && wget https://raw.githubusercontent.com/ilikenwf/apt-fast/master/apt-fast -O /usr/local/sbin/apt-fast \
    && chmod +x /usr/local/sbin/apt-fast \
    && if ! [[ -f /etc/apt-fast.conf ]]; then wget https://raw.githubusercontent.com/ilikenwf/apt-fast/master/apt-fast.conf -O /etc/apt-fast.conf; fi \
    && echo debconf apt-fast/maxdownloads string 16 | debconf-set-selections \
    && echo debconf apt-fast/dlflag boolean true | debconf-set-selections \
    && echo debconf apt-fast/aptmanager string apt-get | debconf-set-selections \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && echo "install some packages" \
    && apt-fast install -y gnupg gosu curl ca-certificates zip unzip git supervisor libcap2-bin libpng-dev \
       php7.0-cli php7.0-dev php7.0-gd \
       php7.0-curl php7.0-memcached \
       php7.0-imap php7.0-mysql php7.0-mbstring \
       php7.0-xml php7.0-zip php7.0-bcmath php7.0-soap \
       php7.0-intl php7.0-readline \
       composer \
       mysql-client \
    && echo "install complete\ninstall nodejs" \
    && curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-fast install -y nodejs \
    && npm install -g npm \
    && echo "npm install complete" \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php7.0

RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/7.0/cli/conf.d/99-mr-unred.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
