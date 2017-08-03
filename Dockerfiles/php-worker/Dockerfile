#
#--------------------------------------------------------------------------
# Image Setup
#--------------------------------------------------------------------------
#
# To take a look at the'php-worker' base Image, visit its DockerHub page
#    https://hub.docker.com/r/nielsvdoorn/laravel-supervisor/
#

FROM nielsvdoorn/laravel-supervisor

MAINTAINER Mahmoud Zalt <mahmoud@zalt.me>

#
#--------------------------------------------------------------------------
# Optional Supervisord Configuration
#--------------------------------------------------------------------------
#
# Modify the ./supervisor.conf file to match your App's requirements.
# Make sure you rebuild your container with every change.
#
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY supervisord_redis_action.ini /etc/supervisor/conf.d/supervisord_redis_action.ini
COPY supervisord_call_back_to_admin.ini /etc/supervisor/conf.d/supervisord_call_back_to_admin.ini
COPY supervisord_clear_timeout_cache.ini /etc/supervisor/conf.d/supervisord_clear_timeout_cache.ini

#
#--------------------------------------------------------------------------
# Optional Software's Installation
#--------------------------------------------------------------------------
#
# If you need to modify this image, feel free to do it right here.
#
	# -- Your awesome modifications go here -- #


#
#--------------------------------------------------------------------------
# Final Touch
#--------------------------------------------------------------------------
#

RUN rm -r /var/lib/apt/lists/*
WORKDIR /etc/supervisor/conf.d/