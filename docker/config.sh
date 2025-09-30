#!/bin/sh

UPDATE_HOSTS=${HAS_SUDO:-true}

HOSTS="platform.publiq.local"

if [ "$UPDATE_HOSTS" = "true" ]; then
        echo "i am here"
  MISSING_HOSTS=""

  set -- $HOSTS
  for HOST; do
    if ! grep -q "$HOST" /etc/hosts; then
      MISSING_HOSTS="$MISSING_HOSTS $HOST"
    fi
  done

  set -- $MISSING_HOSTS
  for MISSING_HOST; do
    echo "$MISSING_HOST has to be in your hosts-file, to add you need sudo privileges"
    sudo sh -c "echo '127.0.0.1 $MISSING_HOST' >> /etc/hosts"
  done
fi

APPCONFIG_ROOTDIR=${APPCONFIG:-'../appconfig'}

DIR="${APPCONFIG_ROOTDIR}/files/platform/docker"
if [ -d "$DIR" ]; then
  cp -R "$DIR"/* .
  mv env .env
else
  echo "Error: missing appconfig. The appconfig repository must be cloned at ${APPCONFIG_ROOTDIR}."
  exit 1
fi
